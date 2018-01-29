"use strict";

var __this = this;

__this.getDefaultValue();

__this.renderElasticTable();

/*
 * Get the indices of elastic search
 */
function getDefaultValue() {
  $.ajax({
    url: '/successive/elastic-index',
    type: "get",
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        var option = '';
        var data = response.data;
        for (var i = 0; i < data.length; i++) {
          option += '<option value="' + data[i].index + '">' + data[i].index + '</option>';
        }
        $('#indexName').append(option);
      }
    },
    error: function (errors) {
      console.error(errors);
    },
    complete: function () {

    }
  });

}

/*
 * Hide the elements
 */
function clearAll() {
  $("#indexType").hide();
  $('#fields').hide();
  $('#searchContainer').hide();
  $('#elastic-table').hide();
  $('#searchValue').val('');
}

/*
 * Get the types of an index
 */
function getTypes() {
  __this.clearAll();

  var selectedIndex = $("#indexName").val();

  if (selectedIndex) {
    $("#indexType").show();

    var formData = {
      index: selectedIndex,
    };

    $.ajax({
      url: '/successive/elastic-index-types',
      type: "get",
      dataType: 'json',
      data: formData,
      success: function (response) {
        if (response.success) {
          var option = '<select id="indexType"  class="form-control" onchange="return getFields()">\n\
                                    <option value="">Select Type</option>';
          var data = response.data;
          for (var i = 0; i < data.length; i++) {
            option += '<option value="' + data[i] + '">' + data[i] + '</option>';
          }
          option += '</select>';
          $("#indexTypeContainer").html(option);
        }
      },
      error: function (errors) {
        console.error(errors);
      },
      complete: function () {

      }
    });
  }

}

/*
 * Get the documents from elastic search
 */
function getResult() {
  var selectedIndex = $("#indexName").val();
  var selectedType = $("#indexType").val();

  if (selectedIndex && selectedType) {
    var formData = {
      index: selectedIndex,
      type: selectedType
    };

    $.ajax({
      url: '/successive/elastic-index-document',
      type: "get",
      dataType: 'json',
      data: formData,
      success: function (response) {
        if (response.success) {
          $("#elastic-table").tabulator("setData", response.data);
        }

      },
      error: function (errors) {
        console.error(errors);
      },
      complete: function () {

      }
    });
  }
}

/*
 * Get the fields for table headers
 */
function getFields() {
  $('#fields').hide();
  $('#searchContainer').hide();
  $('#elastic-table').hide();
  $('#searchValue').val('');

  var selectedIndex = $("#indexName").val();
  var selectedType = $("#indexType").val();

  if (selectedIndex && selectedType) {
    var formData = {
      index: selectedIndex,
      type: selectedType
    };

    $.ajax({
      url: '/successive/elastic-index-fields',
      type: "get",
      dataType: 'json',
      data: formData,
      success: function (resp) {
        if (resp.success) {
          var innerHtml = '';
          var data = resp.data;
          var tabulatorData = new Array();
          for (var i = 0; i < data.length; i++) {
            tabulatorData.push({title: data[i].field, field: data[i].field, sortable: data[i].sortable,
              headerSort: data[i].sortable,
            });
            innerHtml += '<div class="col-md-4"> <input type="checkbox" class="fieldBox" name="field_group[]" id="' +
                    data[i].field + '" value="' + data[i].field + '"> <label for="' + data[i].field + '"> ' +
                    data[i].field + '<label></div>';
          }

          $("#fields").html(innerHtml);
          $('#fields').show();
          $('#searchContainer').show();
          $('input:checkbox').prop('checked', true);
          $("#elastic-table").tabulator("destroy");

          __this.renderElasticTable({tabulatorData: tabulatorData, selectedIndex: selectedIndex, selectedType: selectedType});

          $('#elastic-table').show();
        }
      },
      error: function (errors) {
        console.error(errors);
      },
      complete: function () {

      }
    });
  }
}


/*
 * Show/Hide the column of table
 */
function toggleField(fieldName) {
  $("#elastic-table").tabulator("toggleColumn", fieldName);

  return true;
}

$(document).on('click', '.fieldBox', function () {
  __this.toggleField($(this).prop('value'));
});

/*
 * Render elastic table 
 */
function renderElasticTable(params) {
  var tabulatorData = (typeof params != "undefined" && typeof params.tabulatorData != "undefined") ? params.tabulatorData : [];
  var selectedIndex = (typeof params != "undefined" && typeof params.selectedIndex != "undefined") ? params.selectedIndex : '';
  var selectedType = (typeof params != "undefined" && typeof params.selectedType != "undefined") ? params.selectedType : '';
  $("#elastic-table").tabulator({
    pagination: "remote",
    paginationSize: 50,
    layout: "fitDataFill",
//  responsiveLayout:true,
    placeholder: "No Data Set",
//  height:"400px",
    columns: tabulatorData,
    ajaxURL: "/successive/elastic-index-document", //set url for ajax request
    ajaxSorting: true,
    ajaxParams: {index: selectedIndex, type: selectedType},
    paginationDataSent: {
//    "page":"pageNo", //change page request parameter to "pageNo"
    }
  });

  $('#elastic-table').hide();
}



//trigger download of data.csv file
$("#download-csv").click(function () {
  $("#elastic-table").tabulator("download", "csv", "data.csv");
});

/*
 * Get the search result from elastic search
 */
function performSearch() {
  var searchValue = $('#searchValue').val();
  var selectedIndex = $("#indexName").val();
  var selectedType = $("#indexType").val();

  if (searchValue) {
    $("#elastic-table").tabulator("clearData");

    $("#elastic-table").tabulator("setData", "/successive/elastic-index-document",
            {index: selectedIndex, type: selectedType, search: searchValue});
  }

  return true;
}

/*
 * Process search 
 */
$("#searchForm").submit(function (e) {
  e.preventDefault();
  __this.performSearch();
});


//Clear search result
$("#clearSearch").click(function () {
  if ($('#searchValue').val()) {
      $('#searchValue').val('');
      var selectedIndex = $("#indexName").val();
      var selectedType = $("#indexType").val();
      $("#elastic-table").tabulator("clearData");

      $("#elastic-table").tabulator("setData", "/successive/elastic-index-document",
            {index: selectedIndex, type: selectedType});
  }

  return true;

});
            