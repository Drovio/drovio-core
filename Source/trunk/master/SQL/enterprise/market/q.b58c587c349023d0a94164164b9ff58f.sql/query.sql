SELECT BSS_app_purchase.*
FROM BSS_app_purchase
WHERE BSS_app_purchase.team_id = {tid} AND BSS_app_purchase.application_id = {pid};