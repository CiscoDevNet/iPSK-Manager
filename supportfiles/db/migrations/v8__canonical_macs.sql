-- Reject malformed MACs and canonical collisions before changing any record.
DROP TEMPORARY TABLE IF EXISTS ipsk_mac_preflight;
CREATE TEMPORARY TABLE ipsk_mac_preflight (mac VARCHAR(255) PRIMARY KEY, CHECK (mac REGEXP '^[0-9A-F]{12}$'));
INSERT INTO ipsk_mac_preflight SELECT UPPER(REPLACE(REPLACE(macAddress, ':', ''), '-', '')) FROM endpoints;
START TRANSACTION;
UPDATE endpointAssociations a JOIN endpoints e ON e.id=a.endpointId SET a.macAddress=UPPER(CONCAT_WS(':',SUBSTRING(REPLACE(REPLACE(e.macAddress,':',''),'-',''),1,2),SUBSTRING(REPLACE(REPLACE(e.macAddress,':',''),'-',''),3,2),SUBSTRING(REPLACE(REPLACE(e.macAddress,':',''),'-',''),5,2),SUBSTRING(REPLACE(REPLACE(e.macAddress,':',''),'-',''),7,2),SUBSTRING(REPLACE(REPLACE(e.macAddress,':',''),'-',''),9,2),SUBSTRING(REPLACE(REPLACE(e.macAddress,':',''),'-',''),11,2)));
UPDATE endpoints SET password=LOWER(REPLACE(REPLACE(macAddress,':',''),'-','')),macAddress=UPPER(CONCAT_WS(':',SUBSTRING(password,1,2),SUBSTRING(password,3,2),SUBSTRING(password,5,2),SUBSTRING(password,7,2),SUBSTRING(password,9,2),SUBSTRING(password,11,2)));
COMMIT;
DROP TEMPORARY TABLE ipsk_mac_preflight;
