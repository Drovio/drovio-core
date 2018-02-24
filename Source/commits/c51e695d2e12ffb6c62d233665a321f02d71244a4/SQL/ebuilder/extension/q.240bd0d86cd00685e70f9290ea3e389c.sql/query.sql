SELECT eBLD_extension.id AS extensionID,
	eBLD_extensionLiterals.title AS extensionTitle,
	eBLD_extensionLiterals.description AS extensionDescription,
	eBLD_extension.status AS extensionStatus,
	categoryID, categoryTitle, categoryDescription
FROM eBLD_extension
INNER JOIN  
      ( SELECT eBLD_extensionCategory.id AS categoryID,
                      eBLD_extensionCategoryLiterals.title AS categoryTitle,
                      eBLD_extensionCategoryLiterals.description AS categoryDescription
        FROM eBLD_extensionCategory
        INNER JOIN eBLD_extensionCategoryLiterals
        ON eBLD_extensionCategory.id = eBLD_extensionCategoryLiterals.id 
        WHERE eBLD_extensionCategoryLiterals.locale = '$locale'
      ) AS extensionCategory
ON eBLD_extension.category = categoryID
INNER JOIN eBLD_extensionLiterals
ON eBLD_extension.id = eBLD_extensionLiterals.id 
AND eBLD_extensionLiterals.locale = '$locale'
WHERE eBLD_extension.id = '$id'