UPDATE GLC_language SET 
uniDescription = '$uniDescription', 
nativeDescription = '$nativeDescription', 
languageCode_ISO2_A3 = '$iso2a3', 
languageCode_ISO1_A2 = '$iso1a2'
WHERE GLC_language.id = $id