UPDATE toc_entry
SET label = CONCAT(SUBSTR(label, 1, POSITION("." IN label) - 1), '.', SUBSTR(label, POSITION("." IN label) + 1, 1), '0')
WHERE label LIKE '%.%'
  AND LENGTH(label) = 3;