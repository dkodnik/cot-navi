<!-- BEGIN: MAIN -->
<table class="navi">
	<tr>
		<!-- BEGIN: NAVI_TAB_COL -->
		<td class="coltop">
			<a href="{NAVI_TAB_COL_URL}" title="{NAVI_TAB_COL_DESC}" class="{NAVI_TAB_COL_CURRENT}">
				{NAVI_TAB_COL_TITLE} ({NAVI_TAB_COL_COUNT})
			</a>
		</td>
		<!-- END: NAVI_TAB_COL -->
	</tr>
	<!-- BEGIN: NAVI_TAB_ROW -->
	<tr>
		<!-- BEGIN: NAVI_TAB_ROW_CELL -->
		<td>
			<!-- IF {NAVI_TAB_ROW_CELL_EXISTS} -->
			<a href="{NAVI_TAB_ROW_CELL_URL}" title="{NAVI_TAB_ROW_CELL_DESC}" class="{NAVI_TAB_ROW_CELL_CURRENT}">
				{NAVI_TAB_ROW_CELL_TITLE} ({NAVI_TAB_ROW_CELL_COUNT})
			</a>
			<!-- ENDIF -->
		</td>
		<!-- END: NAVI_TAB_ROW_CELL -->
	</tr>
	<!-- END: NAVI_TAB_ROW -->
</table>
<!-- END: MAIN -->