SELECT e.id, w.name as workname, a.short_name as workauthorname, e.name as translationname, e.year, aed.name, e.source
FROM edition e
         LEFT JOIN work w on e.work_id = w.id
         LEFT JOIN work_author wa on w.id = wa.work_id
         LEFT JOIN author a on a.id = wa.author_id
         LEFT JOIN author_edition ae on e.id = ae.edition_id
         LEFT JOIN author aed on aed.id = ae.author_id
ORDER BY workauthorname, e.year, e.id