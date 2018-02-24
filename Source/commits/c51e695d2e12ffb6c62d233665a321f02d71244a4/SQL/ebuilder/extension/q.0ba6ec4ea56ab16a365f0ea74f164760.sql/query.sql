SELECT eBLD_extensionCategory.id AS categoryID,
       eBLD_extensionCategoryLiterals.title AS categoryTitle
FROM eBLD_extensionCategory
INNER JOIN eBLD_extensionCategoryLiterals
ON  eBLD_extensionCategoryLiterals.locale = '$locale'