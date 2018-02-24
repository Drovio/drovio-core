UPDATE GLC_currency SET 
description = '$description', 
symbol = '$symbol', 
codeISO = '$iso', 
isBase = $base, 
rateToBase = $rate
WHERE GLC_currency.id = $id