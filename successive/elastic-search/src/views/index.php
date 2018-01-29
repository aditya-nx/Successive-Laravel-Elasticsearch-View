
<style>
    .tabulator-table {
        height: 80px !important;
    }
</style>
<div class="row">
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-6 form-group">
                <select id="indexName" class="form-control" onchange="return getTypes()">
                    <option value="">Select Index</option>
                </select>
            </div>
            <div class="col-md-6 form-group" id="indexTypeContainer"></div>
        </div>        
        <div class="row form-group" style="display: none" id="fields"></div>
        
        <form id="searchForm" method="post">
            <div class="row" style="display: none" id="searchContainer" style="margin-bottom: 10px">            
                <div class="col-md-4 form-group">
                    <input class="form-control" type="text" name="searchValue" id="searchValue" placeholder="Enter serach keyword">                
                </div>
                <div class="col-md-2 form-group">
                    <input type="submit" class="form-control btn btn-success" id="performSearch" value="Search">
                </div>  
                <div class="col-md-2 form-group">
                    <button class="form-control btn btn-success" id="clearSearch">Clear Search</button>
                </div>
                <div class="col-md-2 form-group">
                    <button class="form-control btn btn-success" id="download-csv">Download as CSV</button>
                </div>
                <div class="col-md-2"></div>            
            </div>
        </form>
        
        <div id="elastic-table" style="margin-bottom: 100px;"></div>        
    </div>
</div>  