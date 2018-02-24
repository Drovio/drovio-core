UPDATE GLC_timeZone SET 
description = '$description', 
abbreviation = '$abbreviation', 
location = '$location', 
deviationFromGMT = $deviation
WHERE GLC_timeZone.id = $id