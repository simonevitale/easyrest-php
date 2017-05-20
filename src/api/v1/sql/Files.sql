/* SELECT all the files for a given AssetId */

SELECT AssetFile.AssetId, File.FileId, File.OwnerUserId, File.IsPublic, File.OriginalFileName, File.FileName, FileType.FileTypeId, FileType.Name AS 'FileType', FileRole.Name as 'FileRole'
FROM AssetFile
LEFT JOIN FileRole ON AssetFile.FileRoleId = FileRole.FileRoleId
LEFT JOIN File ON AssetFile.FileId = File.FileId
LEFT JOIN FileType ON File.FileTypeId = FileType.FileTypeId
WHERE AssetId = 1
