-- Get next payment id
SELECT IFNULL(MAX(payment_id), 0) + 1 INTO @invoicePaymentID
FROM RTL_invoicePayment
WHERE invoice_id = '{iid}';

-- Set payment id
SELECT CONCAT('{iid}', '_', @invoicePaymentID) INTO @paymentID;

-- Add payment
INSERT INTO RTL_invoicePayment (id, invoice_id, payment_id, payment_type_id, payment, notes, reference_id)
VALUES (@paymentID, '{iid}', @invoicePaymentID, {type}, {payment}, '{notes}', '{ref}');

-- Select payment id
SELECT last_insert_id() as id;