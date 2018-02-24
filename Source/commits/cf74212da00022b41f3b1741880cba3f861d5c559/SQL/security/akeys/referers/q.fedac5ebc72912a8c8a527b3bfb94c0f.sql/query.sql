UPDATE API_keyReferer
SET referer = '{new_referer}'
WHERE akey = '{akey}' AND referer = '{referer}';