-- Set invoice id
SELECT CONCAT_WS('_', {cid}, {type}, {icode}) INTO @invoiceID;

-- Create New Invoice
INSERT INTO RTL_invoice (id, owner_company_id, type_id, invoice_id, time_created, datetime_created, account_id)
VALUES (@invoiceID, {cid}, {type}, @companyInvoiceID, {time}, IF({date} IS NULL, NOW(), '{date}'), {aid});

-- Get invoice id
SELECT @invoiceID AS id;