DELETE
FROM edition
WHERE id NOT IN (SELECT DISTINCT edition_id FROM content);