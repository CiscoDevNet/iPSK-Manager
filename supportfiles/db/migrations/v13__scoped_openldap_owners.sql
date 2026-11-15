USE `{{DB_NAME}}`;
-- A duplicate legacy owner with different scoped candidates deliberately fails
-- before any persistent write. Review ambiguous owners using the README steps.
DROP TEMPORARY TABLE IF EXISTS ipsk_ldap_owner_review_required;
CREATE TEMPORARY TABLE ipsk_ldap_owner_review_required (legacyOwner VARBINARY(255) PRIMARY KEY, scopedOwner VARCHAR(90) CHARACTER SET ascii NOT NULL);
INSERT INTO ipsk_ldap_owner_review_required (legacyOwner,scopedOwner)
SELECT DISTINCT BINARY e.createdBy, CONCAT('OL:',COALESCE(d.identitySourceId,d.id),':',SHA2(CONVERT(LEFT(e.createdBy,CHAR_LENGTH(e.createdBy)-CHAR_LENGTH(d.adDomain)-1) USING utf8mb4),256))
FROM endpoints e JOIN ldapServers d ON d.directoryType=1 AND BINARY RIGHT(e.createdBy,CHAR_LENGTH(d.adDomain)+1)=BINARY CONCAT('-',d.adDomain)
LEFT JOIN ldapServers root ON root.id=d.identitySourceId
WHERE CHAR_LENGTH(e.createdBy)>CHAR_LENGTH(d.adDomain)+1
AND (d.identitySourceId IS NULL OR (root.id IS NOT NULL AND root.identitySourceId IS NULL))
AND e.createdBy NOT REGEXP '^OL:[0-9]+:[a-f0-9]{64}$'
AND e.createdBy NOT REGEXP '^S-[0-9]+(-[0-9]+)+$'
AND NOT EXISTS (SELECT 1 FROM internalUsers u WHERE BINARY u.sid=BINARY e.createdBy);
START TRANSACTION;
UPDATE endpoints e JOIN ipsk_ldap_owner_review_required m ON BINARY e.createdBy=m.legacyOwner SET e.createdBy=m.scopedOwner;
UPDATE endpointAssociations a JOIN endpoints e ON e.id=a.endpointId SET a.createdBy=e.createdBy WHERE BINARY a.createdBy<>BINARY e.createdBy;
-- Cache rows are descriptive only. Preserve an existing scoped cache entry when
-- two deliberate same-source aliases previously used different domain labels.
INSERT INTO userSidCache (sid,userPrincipalName,samAccountName,userDn,createdBy)
SELECT m.scopedOwner,c.userPrincipalName,c.samAccountName,c.userDn,c.createdBy FROM userSidCache c JOIN ipsk_ldap_owner_review_required m ON BINARY c.sid=m.legacyOwner
ON DUPLICATE KEY UPDATE sid=userSidCache.sid;
DELETE c FROM userSidCache c JOIN ipsk_ldap_owner_review_required m ON BINARY c.sid=m.legacyOwner;
COMMIT;
DROP TEMPORARY TABLE ipsk_ldap_owner_review_required;
