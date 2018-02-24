SELECT RB_package.*
FROM RB_package
INNER JOIN RB_packageApplication ON RB_packageApplication.package_id = RB_package.id
WHERE RB_packageApplication.person_id = $person_id