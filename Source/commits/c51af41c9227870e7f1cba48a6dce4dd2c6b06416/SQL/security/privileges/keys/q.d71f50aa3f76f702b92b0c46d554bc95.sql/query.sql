INSERT INTO PLM_accountKey (account_id, userGroup_id, type_id, context, akey, time_created)
VALUES ({aid}, {ugid}, {type}, {context}, '{akey}', {time})
ON DUPLICATE KEY UPDATE akey = '{akey}', time_created = {time};