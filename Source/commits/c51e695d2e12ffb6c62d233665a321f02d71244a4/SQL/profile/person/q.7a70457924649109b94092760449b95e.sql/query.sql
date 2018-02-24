SELECT 
RB_personAddress.id as address_id, 
RB_personAddress.description as description, 
RB_personAddress.address as address, 
RB_personAddress.zipcode as zipcode, 
RB_personAddress.area as area, 
GLC_town.description as town_description, 
GLC_country.countryName as countryName 
FROM RB_personAddress
INNER JOIN RB_person ON RB_person.id = RB_personAddress.person_id 
INNER JOIN PLM_user ON PLM_user.person_id = RB_person.id 
INNER JOIN GLC_town ON GLC_town.id = RB_personAddress.town_id 
INNER JOIN GLC_country ON GLC_country.id = RB_personAddress.country_id 
WHERE PLM_user.id = $uid