<?php

include QUERY_FILE;

class query_reader extends CI_Model
{
    //a variable to hold the cached queries to prevent pulling from the DB for each request
    private $cachedQueries = array();

    //Constructor to set some default values at class load
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        //Use the query cache if its enabled
        $this->load->helper('queries_list');
    }

    //Function which picks the queries from the database
    public function get_query_by_code($queryCode, $queryData = array())
    {
        $cachedQuery = ENABLE_QUERY_CACHE ? get_sys_query($queryCode) : '';
        $queryString = (!empty($cachedQuery) && ENABLE_QUERY_CACHE) ? $cachedQuery : $this->get_raw_query_string($queryCode);

        return !empty($queryString) ? $this->populate_template($queryString, $queryData) : $queryString;
    }

    // Populate the query template with the provided values
    public function populate_template($template, $queryData = array())
    {
        $query = $template;
        // Process the query data to fit the field format expected by the query
        $queryData = $this->format_field_for_query($queryData);

        //replace place holders with actual data required in the string
        foreach ($queryData as $key => $value) {
            $query = str_replace("'".$key."'", "'".$value."'", $query);
        }

        //Then replace any other keys without quotes
        foreach ($queryData as $key => $value) {
            $query = str_replace($key, ''.$value, $query);
        }

        return $query;
    }

    // Returns the raw query string
    private function get_raw_query_string($queryCode)
    {
        // Get the query from the database by the query code
        $qresultArray = $this->db->query("SELECT details FROM query WHERE code = '".$queryCode."'")->row_array();

        return !empty($qresultArray['details']) ? $qresultArray['details'] : '';
    }

    // Returns all fields in the format array('_FIELDNAME_', 'fieldvalue') which is expected by the database
    // query processing function
    public function format_field_for_query($queryData)
    {
        $dataForQuery = array();

        foreach ($queryData as $key => $value) {
            //e.g., $queryData['_LIMIT_'] = "10";
            $dataForQuery['_'.strtoupper($key).'_'] = $value;
        }

        return $dataForQuery;
    }

    //Load queries into the cache file
    public function load_queries_into_cache()
    {
        $queries = $this->db->query('SELECT * FROM query')->result_array();

        //Now load the queries into the file
        file_put_contents(QUERY_FILE, '<?php '.PHP_EOL.'global $sysQuery;'.PHP_EOL);
        foreach ($queries as $query) {
            $queryString = "\$sysQuery['".$query['code']."'] = \"".str_replace('"', '\"', $query['details']).'";'.PHP_EOL;
            file_put_contents(QUERY_FILE, $queryString, FILE_APPEND);
        }

        file_put_contents(QUERY_FILE, PHP_EOL.PHP_EOL.' function get_sys_query($code) { '.PHP_EOL.'global $sysQuery; '.PHP_EOL."return !empty(\$sysQuery[\$code])? \$sysQuery[\$code]: '';".PHP_EOL.' }'.PHP_EOL, FILE_APPEND);

        echo 'QUERY CACHE FILE HAS BEEN UPDATED ['.date('F d, Y H:i:sA T').']';
    }

    // Simply run a query where no result is expected
    public function run($queryCode, $queryData = array())
    {
        return $this->db->query($this->get_query_by_code($queryCode, $queryData));
    }

    // Get the result count for the given query details
    public function get_count($queryCode, $queryData = array())
    {
        return $this->db->query($this->get_query_by_code($queryCode, $queryData))->num_rows();
    }

    // Given the query details, return the result as a single associated array
    public function get_row_as_array($queryCode, $queryData = array())
    {
        return $this->db->query($this->get_query_by_code($queryCode, $queryData))->row_array();
    }

    // Given the query details, return the result as an array of associated arrays
    public function get_list($queryCode, $queryData = array())
    {
        return $this->db->query($this->get_query_by_code($queryCode, $queryData))->result_array();
    }

    // Given the query details that return a single column, return the result as an array
    public function get_single_column_as_array($queryCode, $columnName, $queryData = array())
    {
        $list = array();

        $results = $this->db->query($this->get_query_by_code($queryCode, $queryData))->result_array();
        // check if the column exists in the returned data
        if (!empty($results) && !empty($results[0][$columnName])) {
            foreach ($results as $row) {
                array_push($list, $row[$columnName]);
            }
        }

        return $list;
    }

    // Run an insert query and return the id of the record
    public function add_data($queryCode, $queryData = array())
    {
        $this->db->query($this->get_query_by_code($queryCode, $queryData));

        return $this->db->insert_id();
    }
}
