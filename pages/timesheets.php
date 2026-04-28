<?php
/**
 * Timesheets Management
 */

$page_security = 'SA_TIMESHEET';
$path_to_root = "../../..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/ui.inc");
include_once($path_to_root . "/modules/FA_Timesheets/includes/timesheet_db.inc");

page(_("Timesheets"), false, false, "", "");

$section = isset($_GET['section']) ? $_GET['section'] : 'week';

switch ($section) {
    case 'add':
        display_add_entry();
        break;
    case 'week':
    default:
        display_weekly_timesheet();
        break;
}

end_page(true);

function display_weekly_timesheet(): void
{
    $my_id = isset($_SESSION["wa_user"]) ? $_SESSION["wa_user"]->employee_id : 0;
    $week_start = isset($_GET['week']) ? $_GET['week'] : date('Y-m-d', strtotime('monday this week'));
    $week_end = date('Y-m-d', strtotime($week_start . ' +6 days'));
    
    $entries = get_time_entries(['employee_id' => $my_id, 'start_date' => $week_start, 'end_date' => $week_end]);
    
    start_table(TABLESTYLE);
    table_header([_('Date'), _('Project'), _('Hours'), _('Description'), _('Status')]);
    
    echo "<tr><td colspan='5' align='right'><a href='?section=add'>" . _("Add Entry") . "</a></td></tr>";
    
    while ($entry = db_fetch($entries)) {
        alt_table_row($entry);
        label_cell(sql2date($entry['date']));
        label_cell($entry['project_name'] ?? '-');
        label_cell($entry['hours']);
        label_cell($entry['description'] ?? '-');
        label_cell($entry['status']);
    }
    end_table(1);
}

function display_add_entry(): void
{
    global $Ajax;
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $employee_id = $_POST['employee_id'] ?? 0;
        $date = $_POST['date'] ?? date('Y-m-d');
        $hours = $_POST['hours'] ?? 0;
        $description = $_POST['description'] ?? '';
        
        create_time_entry($employee_id, $date, $hours, null, null, $description);
        display_notification(_("Time entry created"));
        meta_refresh(null, "?section=week");
        $Ajax->activate('content_area');
    }
    
    start_form();
    start_table(TABLESTYLE);
    
    date_row(_("Date:"), 'date');
    numeric_row(_("Hours:"), 'hours', 0, 0, 24, 0.5);
    textarea_row(_("Description:"), 'description', '', 3, 40);
    
    end_table(1);
    submit_center('submit', _("Save Time Entry"));
    end_form();
}