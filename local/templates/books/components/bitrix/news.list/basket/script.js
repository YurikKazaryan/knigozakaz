function save_enable() {
	for(i=1; i<3; i++) {
		$('#btnSave'+i).removeAttr('disabled');
		$('#btnCancel'+i).removeAttr('disabled');
		$('#btnOrder'+i).attr('disabled', 'disabled');
		$('#btnDownload'+i).attr('disabled', 'disabled');
	}
}
