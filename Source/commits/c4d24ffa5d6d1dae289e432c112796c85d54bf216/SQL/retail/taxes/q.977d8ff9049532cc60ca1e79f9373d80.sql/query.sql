SELECT RTL_taxRate.*, GLC_country.countryName
FROM RTL_taxRate
INNER JOIN GLC_country ON RTL_taxRate.country_id = GLC_country.id
WHERE RTL_taxRate.country_id = {cid};