UPDATE BSS_app_purchase
SET version = '{version}'
WHERE BSS_app_purchase.team_id = {tid} AND BSS_app_purchase.application_id = {pid};