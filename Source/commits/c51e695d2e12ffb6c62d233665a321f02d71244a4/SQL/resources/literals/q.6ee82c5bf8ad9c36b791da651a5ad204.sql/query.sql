SELECT TR_literalValue.locale, TR_literalValue.value
FROM TR_literalValue
INNER JOIN GLC_locale ON GLC_locale.locale = TR_literalValue.locale
WHERE TR_literalValue.literal_id = $literal_id AND GLC_locale.default <> 1
ORDER BY TR_literalValue.locale ASC