UPDATE RTL_invoice
SET
	way_of_payment = '{wayOfPayment}',
	purpose_of_trafficking = '{purposeOfTrafficking}',
	way_of_shipping = '{wayOfShipping}',
	shipping_location = '{shippingLocation}',
	delivery_location = '{deliveryLocation}'
WHERE id = '{iid}' AND owner_company_id = {cid};