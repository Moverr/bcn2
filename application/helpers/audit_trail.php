<script type="text/javascript">
     $(document).on('click','.printer',function(){
 
 
    $(".table").printArea();
    
  })


 $(function(){

                       $('.table').dataTable({
                          "paging":   false,
                          "ordering": true,
                          "info":     false ,
                           "processing": true,
                          "serverSide": true,
                          "ajax": "<?=base_url().$search_url; ?>",
                          "dataSrc": "tableData"
                          

                      });

} );



</script><div class="widget">
    <div class="widget-title">
        <h4><i class="fa fa-reorder"></i>&nbsp;AUDIT TRAIL</h4>
            <span class="tools">
                <a href="javascript:;" class="fa fa-chevron-down"></a>
                <a href="javascript:;" class="fa fa-remove"></a>
            </span>
    </div>
    <div class="widget-body" id="results">
    	<?php 

     



			if(!empty($page_list['page_list'])): 
				
				print '<table class="table table-striped table-hover">'.
					  '<thead>'.
					  '<tr>'.
					  '<th width="5%"></th>'.
					  '<th>Name</th>'.
					  '<th class="hidden-480">PDE</th>'.
					  '<th class="hidden-480">URL</th>'.
					  '<th class="hidden-480">IP</th>'.
					  '<th class="hidden-480">BROWSER</th>'.
					  '<th>Date Added</th>'.
					  '</tr>'.
					  '</thead>'.
					  '</tbody>';
					  foreach ($page_list['page_list'] as $key => $row) {
					  	# code...

   print  '<tr>'.
					  '<th width="5%"></th>'.
					  '<th>'.$row['user_full_names'].'</th>'.
					  '<th class="hidden-480">'.$row['pde'].'</th>'.
					  '<th class="hidden-480">'.$row['url'].'</th>'.
					  '<th class="hidden-480">'.$row['ipaddress'].'</th>'.
					  '<th class="hidden-480">'.$row['browser'].'</th>'.
					  '<th>Date Added</th>'.
					  '</tr>';

					  #	print_r($row);
					 	
				 
				}
				
				
				print '</tbody></table>';
				
				print '<div class="pagination pagination-mini pagination-centered">'.
						pagination($this->session->userdata('search_total_results'), $page_list['rows_per_page'], $page_list['current_list_page'], base_url().	
						"admin/audit_trail/p/%d")
					.'</div>';
		
			else:
        		print format_notice('WARNING: No users have been added to the system');
        	endif; 
        ?>
    </div>
</div>