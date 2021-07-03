DELETE
FROM edition
WHERE id NOT IN (SELECT DISTINCT edition_id FROM content);


DELETE
FROM work
WHERE id NOT IN (SELECT DISTINCT work_id FROM edition);


DELETE
FROM toc_entry
WHERE id NOT IN (SELECT DISTINCT toc_entry_id FROM content);



DELETE
FROM author
WHERE id NOT IN (SELECT author_id FROM work_author)
AND id NOT IN (SELECT author_id FROM author_edition);