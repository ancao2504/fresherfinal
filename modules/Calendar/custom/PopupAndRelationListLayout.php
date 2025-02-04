<?php

$popupLayout = array(
    'display_fields' => array(
        'subject',
        'parent_id',
        'date_start',
        'time_start',   // Do not remove
        'due_date',
        'time_end',     // Do not remove
        'recurringtype',
        'activitytype',
        'visibility',
        'assigned_user_id',
        'related_campaign',
    ),
    'sort_field' => 'date_start',
    'sort_order' => 'DESC'
);

$relationListLayout = array(
    'display_fields' => array(
        'subject',
        'parent_id',
        'date_start',
        'time_start',   // Do not remove
        'due_date',
        'time_end',     // Do not remove
        'activitytype',
        'description',
        'recurringtype',
        'visibility',
        'assigned_user_id',
        'related_campaign',
    ),
    'sort_field' => 'date_start',
    'sort_order' => 'DESC'
);