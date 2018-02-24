SELECT node.id, node.description, (COUNT(parent.description) - (sub_tree.depth + 1)) AS depth 
FROM UNIT_moduleGroup AS node, UNIT_moduleGroup AS parent, UNIT_moduleGroup AS sub_parent, 
( 
	SELECT node.description, (COUNT(parent.description) - 1) AS depth 
	FROM UNIT_moduleGroup AS node, UNIT_moduleGroup AS parent 
	WHERE node.lft BETWEEN parent.lft AND parent.rgt AND node.id = $pid 
	GROUP BY node.description 
	ORDER BY node.lft 
) AS sub_tree 
WHERE node.lft BETWEEN parent.lft AND parent.rgt 
AND node.lft BETWEEN sub_parent.lft AND sub_parent.rgt 
AND sub_parent.description = sub_tree.description 
GROUP BY node.id 
HAVING depth BETWEEN $from_depth AND $to_depth 
ORDER BY node.lft