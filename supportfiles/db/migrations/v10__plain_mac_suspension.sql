USE `{{DB_NAME}}`;
DROP PROCEDURE IF EXISTS `iPSK_AuthMACPlain`;
DELIMITER $$
CREATE DEFINER={{PLAIN_MAC_DEFINER}} PROCEDURE `iPSK_AuthMACPlain` (IN `username` VARCHAR(64), IN `password` VARCHAR(255))  SQL SECURITY INVOKER
BEGIN
	SELECT UCASE(REPLACE(REPLACE(username,':',''),'-','')) INTO @strippedMAC;
	
	SELECT CONCAT_WS(':',SUBSTRING(@strippedMAC,1,2),SUBSTRING(@strippedMAC,3,2),SUBSTRING(@strippedMAC,5,2),SUBSTRING(@strippedMAC,7,2),SUBSTRING(@strippedMAC,9,2),SUBSTRING(@strippedMAC,11,2)) INTO @formattedMAC;

	IF NOT (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC) = 0 THEN
		IF NOT (SELECT accountExpired FROM endpoints WHERE endpoints.macAddress = @formattedMAC) = 'True' THEN
			IF (SELECT expirationDate FROM endpoints WHERE endpoints.macAddress = @formattedMAC) < UNIX_TIMESTAMP(NOW()) THEN
				UPDATE `endpoints` SET `endpoints`.`accountExpired` = 'True' WHERE `endpoints`.`macAddress` = @formattedMAC;
			END IF;
		END IF;
	END IF;
	
	IF EXISTS (SELECT * FROM endpoints WHERE endpoints.macAddress = @formattedMAC AND endpoints.password = password AND endpoints.accountEnabled = 1) THEN
		SELECT 0,11,'This is a very good user, give him all access','no error';
	ELSE
		SELECT 3, 0, 'odbc','ODBC Authen Error';
	END IF;
END$$
DELIMITER ;
