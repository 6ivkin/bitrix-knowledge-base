<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>








<ol>
<?foreach ($arResult['TREE'] as $item1) {?>

	<?//LVL1?>
	
	<li class="section">
		<?if($item1['IS_OPENED_CURRENT'] == 'Y'){?><em><?}?>
		<a href="<?=$item1['SECTION_PAGE_URL']?>" style="font-size: 1.2rem;"><?=$item1['NAME']?></a>
		<?if($item1['IS_OPENED_CURRENT'] == 'Y'){?></em><?}?>
	</li>
	
	<?if($item1['HAS_CHILDRENS'] == 'Y' && $item1['IS_OPENED']){?>
		<li>
			<ol>
				<?foreach ($item1['children'] as $item2) {?>
				
					<?//LVL2?>
					<?if($item2['HAS_CHILDRENS'] == 'Y'){?>
	
						<li>
							<details <?if($item2['IS_OPENED'] == 'Y'){?>open<?}?>>
								<summary style="font-size: 1rem;"><?=$item2['NAME']?></summary>
								<ol>
									
									
									
									<?foreach ($item2['children'] as $item3) {?>
				
										<?//LVL3?>
										<?if($item3['HAS_CHILDRENS'] == 'Y'){?>
						
											<li>
												<details <?if($item3['IS_OPENED'] == 'Y'){?>open<?}?>>
													<summary style="font-size: 0.9rem;"><?=$item3['NAME']?></summary>
													<ol>
													
														
														
														
														
														<?foreach ($item3['children'] as $item4) {?>
									
															<?//LVL4?>
															<?if($item4['HAS_CHILDRENS'] == 'Y'){?>
											
																<li>
																	<details <?if($item4['IS_OPENED'] == 'Y'){?>open<?}?>>
																		<summary style="font-size: 0.8rem;"><?=$item4['NAME']?></summary>
																		<ol>
																		
																		
																			<?foreach ($item4['children'] as $item5) {?>
																				<?//LVL4?>
																				<li>
																					<?if($item5['IS_OPENED_CURRENT'] == 'Y'){?><em><?}?>
																					<a href="<?=$item5['SECTION_PAGE_URL']?>" style="font-size: 0.7rem;"><?=$item5['NAME']?></a>
																					<?if($item5['IS_OPENED_CURRENT'] == 'Y'){?></em><?}?>
																				</li>
																			<?}?>
																	
																			
																			
																			
																			
																			
																			
																			
																		</ol>	
																	</details>
																</li>

															<?}else{?>
																<li>
																	<?if($item4['IS_OPENED_CURRENT'] == 'Y'){?><em><?}?>
																	<a href="<?=$item4['SECTION_PAGE_URL']?>" style="font-size: 0.9rem;"><?=$item4['NAME']?></a>
																	<?if($item4['IS_OPENED_CURRENT'] == 'Y'){?></em><?}?>
																</li>
															<?}?>
														
														
														<?}?>





														
														
													</ol>	
												</details>
											</li>

										<?}else{?>
											<li>
												<?if($item3['IS_OPENED_CURRENT'] == 'Y'){?><em><?}?>
												<a href="<?=$item3['SECTION_PAGE_URL']?>" style="font-size: 0.9rem;"><?=$item3['NAME']?></a>
												<?if($item3['IS_OPENED_CURRENT'] == 'Y'){?></em><?}?>
											</li>
										<?}?>
									
									
									<?}?>
									
									
									
								</ol>	
							</details>
						</li>

					<?}else{?>
						<li>
							<?if($item2['IS_OPENED_CURRENT'] == 'Y'){?><em><?}?>
							<a href="<?=$item2['SECTION_PAGE_URL']?>" style="font-size: 1rem;"><?=$item2['NAME']?></a>
							<?if($item2['IS_OPENED_CURRENT'] == 'Y'){?></em><?}?>
						</li>
					<?}?>

				<?}?>
			</ol>	
		</li>
	<?}?>
	
<?}?>

</ol>



