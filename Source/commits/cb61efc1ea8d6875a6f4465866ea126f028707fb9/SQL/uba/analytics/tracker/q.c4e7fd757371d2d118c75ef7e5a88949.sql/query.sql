UPDATE event_aggregate SET counter=counter+1
WHERE event_name = '{event_name}' AND user_id ='{user_id}' AND session_id = '{session_id}'