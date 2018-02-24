-- Get next invoice id
SELECT IFNULL(MAX(invoice_id), 0) + 1 INTO @companyInvoiceID
FROM RTL_invoice
WHERE owner_company_id = {cid};

-- Set invoice id
SELECT CONCAT({cid}, '_', @companyInvoiceID) INTO @invoiceID;

-- Create New Invoice
INSERT INTO RTL_invoice (id, owner_company_id, invoice_id, type_id, time_created)
VALUES (@invoiceID, {cid}, @companyInvoiceID, {type}, {time});

-- Get invoice id
SELECT @invoiceID AS id;