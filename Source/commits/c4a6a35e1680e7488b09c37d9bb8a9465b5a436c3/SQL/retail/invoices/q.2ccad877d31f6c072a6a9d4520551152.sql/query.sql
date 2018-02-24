-- Get next invoice id
SELECT IFNULL(MAX(invoice_id), 0) + 1 INTO @companyInvoiceID
FROM RTL_invoice
WHERE owner_company_id = {cid} AND type_id = {type};

-- Set invoice id
SELECT CONCAT_WS('_', {cid}, {type}, @companyInvoiceID) INTO @invoiceID;

-- Create New Invoice
INSERT INTO RTL_invoice (id, owner_company_id, type_id, invoice_id, time_created, datetime_created, account_id)
VALUES (@invoiceID, {cid}, {type}, @companyInvoiceID, {time}, IF({date} IS NULL, NOW(), '{date}'), {aid});

-- Get invoice id
SELECT @invoiceID AS id;