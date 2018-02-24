SELECT RTL_invoiceProduct.*, RTL_product.m_unit_id
FROM RTL_invoiceProduct
INNER JOIN RTL_product ON RTL_product.id = RTL_invoiceProduct.product_id
WHERE RTL_invoiceProduct.invoice_id = '{iid}';