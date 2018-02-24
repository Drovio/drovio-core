UPDATE RTL_invoicePayment
SET
	payment = {payment},
	notes = '{notes}',
	reference_id = '{ref}'
WHERE id = '{id}' AND invoice_id = '{iid}';