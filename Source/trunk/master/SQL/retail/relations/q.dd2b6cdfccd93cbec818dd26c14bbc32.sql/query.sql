SELECT *
FROM RTL_invoice
WHERE owner_company_id = {cid} AND customer_id = '{pid}' AND completed = 1
ORDER BY time_created DESC, date_created DESC;