<?php

use Attendance\Contract;

if (isset($content)) {
	if (isset($content['add'])) { // Add Button (Contracts)
		$this->add();

		return;
	} elseif (isset($content['con_table']['delete'])) { // Delete Button
		list($id) = each($content['con_table']['delete']);
		Contract::Delete($id);
	} elseif (isset($content['con_table']['edit'])) { // Edit Button
		list($id) = each($content['con_table']['edit']);
		$this->edit($content = null, $id);

		return;
	} elseif (isset($content['expired'])) {
		$this->so->toogle_disabled_contracts();
	}
}

$content['con_table'] = $this->Contracts->Load();
echo "<div><script type='text/javascript'>
						window.onload = function(){
							var rows = document.querySelectorAll('span');
							var status, searchText;
							for (var i = 0; i < rows.length; i++) {
							  status = rows[i];
							  searchText = 'Abgelaufen';
							  if (status.textContent == searchText) {
							  	 status.parentNode.parentNode.style.background = '#ffbbbb';
							  	 status.parentNode.parentNode.style.opacity = '0.5';
							  }
							}
						}
						</script></div>";
$GLOBALS['egw_info']['flags']['app_header'] = lang('attendance - Manage');
