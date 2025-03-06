<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();



function activeSectionsTree($sectonId, $iblockId){
	
	$arSections = [];

	$dbSections = CIBlockSection::GetNavChain(
		$iblockId,
		$sectonId,
		['ID', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL', 'NAME', 'SECTION_PAGE_URL'],
		$arrayResult = false
	);

	while($arSection = $dbSections->GetNext()) {
		$arSections[$arSection['ID']] = $arSection;
	}

	return $arSections;
}




function buildTree(array $elements, $parentId = 0, $activeSectionsTree, $sectonId){
	
    $branch = [];

    foreach ($elements as $element) {
        if ($element['IBLOCK_SECTION_ID'] == $parentId) {
            $children = buildTree($elements, $element['ID'], $activeSectionsTree, $sectonId);
            if ($children) {
                $element['children'] = $children;
				$element['HAS_CHILDRENS'] = 'Y';
            }
			
			if($activeSectionsTree[$element['ID']] > 0){
				$element['IS_OPENED'] = 'Y';
				
				if($element['ID'] == $sectonId){
					$element['IS_OPENED_CURRENT'] = 'Y';
				}
			}
			
            $branch[] = $element;
        }
    }

    return $branch;
}





$activeSectionsTree = activeSectionsTree($arParams['CUR_SECTION_ID'], $arParams['IBLOCK_ID']);
$tree = buildTree($arResult['SECTIONS'], $arResult['SECTION']['ID'], $activeSectionsTree, $arParams['CUR_SECTION_ID']);


$arResult['TREE'] = $tree;



//d($arResult['TREE'][1]['children'][2]);





