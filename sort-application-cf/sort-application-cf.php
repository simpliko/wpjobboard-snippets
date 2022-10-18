<?php
/**
Plugin Name: WPJobBoard Snippets - Sort Application CF
Version: 1.0
Author: Greg Winiarski
Description: Allow to enable sorting by custom fields columns in the wp-admin / Job Board / Applications panel.
*/

class WPJB_Sort_Application_Cf {
    
    public $field_name = "";
    
    public $column_name = "";
    
    public $label = "";
    
    public function __construct( $field_name, $column_name, $label ) {
        $this->field_name = $field_name;
        $this->column_name = $column_name;
        $this->label = $label;
        
        add_action( "wpjb_custom_columns_head", array( $this, "columns_head" ) );
        add_action( "wpjb_custom_columns_body", array( $this, "columns_body" ), 10, 2 );
        
        add_filter( "wpjb_sort_applications_allowed_fields", array( $this, "allowed_fields" ) );
        add_filter( "wpjb_applications_query", array( $this, "query" ) );
    }
    
    public function allowed_fields( $fields ) {
        $fields[] = $this->column_name;
        return $fields;
    }
    
    public function query( $select ) {
    
        if(Daq_Request::getInstance()->get("sort") != $this->column_name) {
            return $select;
        }
    
        if(Daq_Request::getInstance()->get("order") == "asc") {
            $order = "ASC";
        } else {
            $order = "DESC";
        }
        
        $query = new Daq_Db_Query();
        $query->select("t.id");
        $query->from("Wpjb_Model_Meta t");
        $query->where("t.name = ?", $this->field_name);
        $query->where("t.meta_object = ?", "apply");
        $query->limit(1);
        
        $meta_id =  absint($query->fetchColumn());
        
        $select->order("__{$this->field_name}.value $order");
        $select->joinLeft("t1.meta __{$this->field_name}", "(__{$this->field_name}.meta_id = $meta_id)");
        
        return $select;
    }
    
    public function columns_head( $type ) {
        if( $type !== "application" ) {
            return;
        }
        $sort = Daq_Request::getInstance()->get("sort");
        $order = Daq_Request::getInstance()->get("order");
        
        $order = ( $order === "desc" ) ? "desc" : "asc";
        
        ?>
        <th style="" class="sortable <?php wpjb_column_sort($sort==$this->column_name, $order) ?>" scope="col">
            <a href="<?php echo esc_attr(add_query_arg(array("sort"=>$this->column_name, "order"=>wpjb_column_order($sort==$this->column_name, $order)))) ?>">
                <span><?php echo esc_html( $this->label ) ?></span>
                <span class="sorting-indicator"></span>
            </a>
        </th> 
        <?php
    }
    
    public function columns_body( $type, $item ) {
        if( $type !== "application" ) {
            return;
        }
        ?>
        <td data-colname="<?php esc_attr_e("Status", "wpjobboard") ?>">
            <?php echo $item->meta->{$this->field_name}->value() ?>
        </td>
        <?php
    }
    
    
}

$cf = new WPJB_Sort_Application_Cf("education_level", "__cf", "Education Level");
