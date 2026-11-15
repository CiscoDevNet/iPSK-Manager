USE `{{DB_NAME}}`;
UPDATE endpointAssociations a JOIN endpoints e ON e.id=a.endpointId SET a.createdBy=e.createdBy WHERE BINARY a.createdBy <> BINARY e.createdBy;
