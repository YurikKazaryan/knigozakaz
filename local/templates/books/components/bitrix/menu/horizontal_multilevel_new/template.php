<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>

	<?if ($USER->IsAdmin()):?>
		<style>body {padding-top:0;}</style>
	<?endif;?>

	<nav class="navbar <?if (!$USER->IsAdmin()):?>navbar-fixed-top<?endif;?> navbar-main">
		<div class="container">
			<ul class="nav navbar-nav">

				<? $previousLevel = 0; ?>
				<?foreach ($arResult as $arItem):?>

					<?if ($previousLevel && $arItem["DEPTH_LEVEL"] < $previousLevel):?>
						<?=str_repeat("</ul></li>", ($previousLevel - $arItem["DEPTH_LEVEL"]));?>
					<?endif;?>

					<?if ($arItem["IS_PARENT"]):?>
						<li class="dropdown<?if ($arItem["SELECTED"]):?> active<?endif;?>">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><?=$arItem["TEXT"]?> <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
					<?else:?>
						<?if ($arItem["PERMISSION"] > "D"):?>
							<?if ($arItem["DEPTH_LEVEL"] == 1):?>
								<li <?if ($arItem["SELECTED"]):?>class="active"<?endif;?>><a href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?></a></li>
							<?else:?>
								<?if ($arItem["TEXT"] == '-'):?>
									<li role="separator" class="divider"></li>
								<?else:?>
									<li <?if ($arItem["SELECTED"]):?>class="active"<?endif;?>><a href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?></a></li>
								<?endif;?>
							<?endif?>
						<?else:?>
							<?if ($arItem["DEPTH_LEVEL"] == 1):?>
								<li <?if ($arItem["SELECTED"]):?>class="active"<?endif;?>><a href="" title="<?=GetMessage("MENU_ITEM_ACCESS_DENIED")?>"><?=$arItem["TEXT"]?></a></li>
							<?else:?>
								<li><a href="" class="denied" title="<?=GetMessage("MENU_ITEM_ACCESS_DENIED")?>"><?=$arItem["TEXT"]?></a></li>
							<?endif;?>
						<?endif;?>
					<?endif;?>

					<?$previousLevel = $arItem["DEPTH_LEVEL"];?>

				<?endforeach;?>

				<?if ($previousLevel > 1)://close last item tags?>
					<?=str_repeat("</ul></li>", ($previousLevel-1) );?>
				<?endif?>

			</ul>

			<?if (0):?>
				<div class="btn-group btn-group-xs navbar-btn navbar-right">
					<?if ($USER->IsAuthorized()):?>
						<a class="btn btn-default" href="/?logout=yes" title="Выход (<?=$USER->GetFullName()?>)"><span class="glyphicon glyphicon-log-out"></span> Выход</a>
					<?else:?>
						<a class="btn btn-default" href="/auth/" title="Вход"><span class="glyphicon glyphicon-log-in"></span> Вход</a>
					<?endif;?>
				</div>
			<?endif;?>

		</div> <!-- // class="container-fluid" -->
	</nav>

<?endif;?>