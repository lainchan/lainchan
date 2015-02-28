/*
 * fix-report-delete-submit.js
 *
 * Usage:
 *   $config['additional_javascript'][] = 'js/jquery.min.js';
 *   $config['additional_javascript'][] = 'js/post-menu.js';
 *   $config['additional_javascript'][] = 'js/fix-report-delete-submit.js';
 *
 */

if (active_page == 'thread' || active_page == 'index') {
$(document).ready(function(){

if ($('.delete #password').length) {
	Menu.add_item("delete_post_menu", "Delete post");
	Menu.add_item("delete_file_menu", "Delete file");
	Menu.onclick(function(e, $buf) {
		var ele = e.target.parentElement.parentElement;
		var $ele = $(ele);
		var threadId = $ele.parent().attr('id').replace('thread_', '');
		var postId = $ele.find('.post_no').not('[id]').text();

		$buf.find('#delete_post_menu,#delete_file_menu').click(function(e) {
			e.preventDefault();
			$('#delete_'+postId).prop('checked', 'checked');
		
			if ($(this).attr('id') === 'delete_file_menu') {
				$('#delete_file').prop('checked', 'checked');
			} else {
				$('#delete_file').prop('checked', '');
			}
			$('input[name=delete][type=submit]').click();
		});
	});
}

Menu.add_item("report_menu", "Report");
Menu.add_item("global_report_menu", "Global report");
Menu.onclick(function(e, $buf) {
	var ele = e.target.parentElement.parentElement;
	var $ele = $(ele);
	var threadId = $ele.parent().attr('id').replace('thread_', '');
	var postId = $ele.find('.post_no').not('[id]').text();

	$buf.find('#report_menu,#global_report_menu').click(function(e) {
		$('#delete_'+postId).prop('checked', 'checked');
		if ($(this).attr('id') === 'global_report_menu') {
			header = "<div><h1>Attention!</h1><p>This form is only for reporting <strong>child pornography</strong>, <strong>bot spam</strong> and <strong>credit card numbers, social security numbers or banking information</strong>. DMCA requests and all other deletion requests <em>MUST</em> be sent via email to admin@8chan.co.</p><p>8chan is unmoderated and allows posts without collecting <em>ANY</em> information from the poster less the details of their post. Furthermore, all boards on 8chan are user created and not actively monitored by anyone but the board creator.</p><p>8chan has a small volunteer staff to handle this queue, please do not waste their time by filling it with nonsense! <em>If you made a report with this tool and the post was not deleted, <strong>do not make the report again!</strong> Email admin@8chan.co instead.</em> Abuse of the global report system could lead to address blocks against your IP from 8chan.</p><p>Again, 8chan's global volunteers <em>do not</em> handle board specific issues. You most likely want to click \"Report\" instead to reach the creator and volunteers he assigned to this board.</p>";
			$('#global_report').prop('checked', 'checked');
		} else {
			header = "";
			$('#global_report').prop('checked', '');
		}
		alert(header+"Enter reason below...<br/><input type='text' id='alert_reason'>", true, function(){
			$('#reason').val($('#alert_reason').val());
			$('input[name=report][type=submit]').click();
		});

	});
});

$(document).on('new_post', function(){
	$('div.delete').hide();
	$('input.delete').hide();
});
$('div.delete').hide();
$('input.delete').hide();
})}
