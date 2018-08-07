<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class show_List_Table_of_like_post extends WP_List_Table
{
    
    /**
        * [REQUIRED] this is a default column renderer
        *
        * @param $item - row (key, value array)
        * @param $column_name - string (key)
        * @return HTML
        */
    function column_default($item, $column_name)
    {
        return $item[$column_name];
    }

 /**
        * [REQUIRED] this is a cb column renderer
        *
        * @param $item - row (key, value array)
        * @param $column_name - string (key)
        * @return HTML
        */
  
    function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['post_id']
        );
    }
    
    /**
        * [REQUIRED] This method return columns to display in table
        * you can skip columns that you do not want to show
        * like content, or description
        *
        * @return array
        */
    function get_columns()
    {
        $columns = array(
            'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
            'post_id' => __('Name', 'custom_table_example'),
            'post_title' => __('title', 'custom_table_example'),
            'guid' => __('delete', 'custom_table_example'),
        );
        return $columns;
    }

    /**
        * [OPTIONAL] This method return columns that may be used to sort table
        * all strings in array - is column names
        * notice that true on name column means that its default sort
        *
        * @return array
        */
    function get_sortable_columns()
    {
        $sortable_columns = array(
           
        );
        return $sortable_columns;
    }

    /**
        * [OPTIONAL] Return array of bult actions if has any
        *
        * @return array
        */
    function get_bulk_actions()
    {
        $actions = array(
            'delete' => 'Delete'
        );
        return $actions;
    }

    /**
        * [OPTIONAL] This method processes bulk actions
        * it can be outside of class
        * it can not use wp_redirect coz there is output already
        * in this example we are processing delete action
        * message about successful deletion will be shown on page in next part
        */
    protected function column_guid( $item ) {
		$page = wp_unslash( $_REQUEST['page'] ); // WPCS: Input var ok.
		// Build edit row action.
		
		$delete_query_args = array(
			'page'   => $page,
			'action' => 'delete',
			'post_id'  => $item['post_id'],
			'user_id'  => $item['post_id'],
		);
		$actions['delete'] = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( wp_nonce_url( add_query_arg( $delete_query_args, 'admin.php' ), 'deletemovie_' . $item['post_id'] ) ),
			_x( 'Delete', 'List table row action', 'wp-list-table-example' )
		);
		// Return the title contents.
		return sprintf( '%1$s <span style="color:silver;">(id:%2$s)</span>%3$s',
			$item['post_title'],
			$item['post_id'],
			$this->row_actions( $actions )
		);
	}

    function process_bulk_action()
    {
        global $wpdb;
          $table_name = 'wp_post_user_like'; // do not forget about tables prefix
$user_id=get_current_user_id();
        if ('delete' === $this->current_action() ) {
            
             $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce,'deletemovie_' . $_REQUEST['post_id'] ) ) {
                die( 'Go get a life script kiddies' );
            }else{
            $ids = isset($_REQUEST['post_id']) ? $_REQUEST['post_id'] : array();
            if (is_array($ids)) $ids = implode(',', $ids);

            if (!empty($ids)) {
                $wpdb->query("DELETE FROM $table_name WHERE post_id=$ids and user_id=$user_id");
            }
            }
        }
    }
    
    protected function column_post_title( $item ) {
		return sprintf(
			'<a href= "%s">%s</a>',
			$item['guid'] ,  
			$item['post_title']               
		);
	}

    /**
        * [REQUIRED] This is the most important method
        *
        * It will get rows from database and prepare them to be showed in table
        */
    function prepare_items()
    {
        global $wpdb;
        $table_name = 'wp_post_user_like'; // do not forget about tables prefix

        $per_page = 5; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array() ;
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();
$user_id=get_current_user_id();
        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(post_id) FROM $table_name WHERE user_id=$user_id");

        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? ($per_page * max(0, intval($_REQUEST['paged']) - 1)) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'name';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

        
        $this->items = $wpdb->get_results($wpdb->prepare("SELECT wp_post_user_like.post_id , wp_posts.post_title, wp_posts.guid FROM wp_post_user_like LEFT JOIN wp_posts ON wp_posts.ID=post_id WHERE user_id=$user_id  LIMIT %d OFFSET %d", $per_page, $paged), ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }

}
$Custom_Table_Example_List_Table=new show_List_Table_of_like_post();
$Custom_Table_Example_List_Table->prepare_items();
$Custom_Table_Example_List_Table->display();
