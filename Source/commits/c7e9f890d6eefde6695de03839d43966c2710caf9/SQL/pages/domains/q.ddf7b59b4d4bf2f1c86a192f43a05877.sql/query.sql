SELECT *, PG_domain.name as domain_name
FROM PG_domain
INNER JOIN PG_pageFolder ON PG_pageFolder.domain = PG_domain.name
GROUP BY PG_domain.name
ORDER BY PG_domain.name ASC;