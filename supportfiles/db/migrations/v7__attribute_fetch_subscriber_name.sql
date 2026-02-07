/*
 * Migration: add subscriberName to iPSK_AttributeFetch for WLC username visibility.
 */

USE `{{DB_NAME}}`;

DROP PROCEDURE IF EXISTS `iPSK_AttributeFetch`;

DELIMITER $$
CREATE DEFINER=`{{ISE_DB_USERNAME}}`@`%` PROCEDURE `iPSK_AttributeFetch` (IN `username` VARCHAR(64), OUT `result` INT)  SQL SECURITY INVOKER
BEGIN
	IF username = '*' THEN
		SELECT username INTO @formattedMAC;
	ELSE
		SELECT UCASE(REPLACE(REPLACE(username,':',''),'-','')) INTO @strippedMAC;
	
		SELECT CONCAT_WS(':',SUBSTRING(@strippedMAC,1,2),SUBSTRING(@strippedMAC,3,2),SUBSTRING(@strippedMAC,5,2),SUBSTRING(@strippedMAC,7,2),SUBSTRING(@strippedMAC,9,2),SUBSTRING(@strippedMAC,11,2)) INTO @formattedMAC;
	END IF;
	
	CASE @formattedMAC
	WHEN '*' THEN
		SET result=0;
		SELECT 'Empty' AS fullName, 'Empty' AS emailAddress, 'Empty' AS createdBy, 'Empty' AS description, '0' AS expirationDate, 'False' AS accountExpired, 'EMPTY' AS pskValue, 'EMPTY' as pskValuePlain, 'Empty' AS vlan, 'Empty' AS dacl, 'subscriber:username=Empty' AS subscriberName;
	ELSE
	  IF EXISTS (SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC) THEN
		SET result=0;
		SELECT fullName,emailAddress,createdBy,description,expirationDate,accountExpired,pskValue, RIGHT(pskValue, LENGTH(pskValue) - 4) as pskValuePlain,vlan,dacl, CONCAT('subscriber:username=', COALESCE(NULLIF(fullName,''), NULLIF(description,''), createdBy)) AS subscriberName FROM endpoints WHERE endpoints.macAddress = @formattedMAC;
	  ELSE
		SET result=1;
	  END IF;
	END CASE;
END$$
DELIMITER ;

