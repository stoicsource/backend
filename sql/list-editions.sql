SELECT e.id, w.name as workname, a.short_name as workauthorname, aed.name, e.name as translationname, e.year, e.quality, e.source
FROM edition e
         LEFT JOIN work w on e.work_id = w.id
         LEFT JOIN author a on a.id = w.author_id
         LEFT JOIN author aed on aed.id = e.author_id
ORDER BY workauthorname, e.year, e.id