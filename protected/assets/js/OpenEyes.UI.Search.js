/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

/**
 *  HOW TO USE
    
    Intit the search by calling the
    OpenEyes.UI.Search.init( $('#patient_merge_search') );
    and pass the input DOM
    Clik on one item in the autocomplete list will redirect to the patien's summery page like:  /patient/view/19942

    To override the default functionality
    
    Set the ajax URL to be called (default: /patient/ajaxSearch ):

    OpenEyes.UI.Search.setSourceURL('/patientMergeRequest/search');

    To override the jquery autocomplete defaults:

    use the OpenEyes.UI.Search.getElement() to get back the input DOM with jquery autocomple

    _renderItem:
    OpenEyes.UI.Search.getElement().data('autocomplete')._renderItem = function (ul, item) {
        return $("<li></li>")
          .data("item.autocomplete", item)
          .append("<a><strong>" + item.first_name + " " + item.last_name + "</strong></a>")
          .appendTo(ul);
    };

    select:
    OpenEyes.UI.Search.getElement().autocomplete('option', 'select', function(event, ui){
        alert(ui.item.id);   
    });

    close:
    OpenEyes.UI.Search.getElement().autocomplete('option', 'close', function(event, ui){
        console.log(event, ui);
    });

    
    
 * 
 * 
 */

(function (exports) {
  /**
   * OpenEyes UI namespace
   * @namespace OpenEyes.UI
   * @memberOf OpenEyes
   */
  
    var autocompleteSource = '/patient/ajaxSearch';

    function initAutocomplete() {

        this.$searchInput.autocomplete({
            minLength: 0,
            source: function (request, response) {
                $.getJSON(autocompleteSource, {
                    term: request.term,
                    ajax: 'ajax',
                }, response);
            },
            search: function () {
                $('.loader').show();
            },
            select: function(event, ui){
                window.location.href = "/patient/view/" + ui.item.id;
            },
            response: function (event, ui) {
                $('.loader').hide();
                if (ui.content.length === 0) {
                    $('.no-result-patients').slideDown();
                } else {
                    $('.no-result-patients').slideUp();
                }
            },
        });

        if (this.$searchInput !== 'undefined' && this.$searchInput.length) {
            this.$searchInput.data("autocomplete")._renderItem = function (ul, item) {
                ul.addClass("z-index-1000 patient-ajax-list");
                return $("<li></li>")
                    .data("item.autocomplete", item)
                    .append("<a><strong>" + item.first_name + " " + item.last_name + "</strong>" + " (" + item.age + ")" + "<span class='icon icon-alert icon-alert-" + item.gender.toLowerCase() + "_trans'>Male</span>" + "<div class='nhs-number'>" + item.nhsnum + "</div><br>Hospital No.: " + item.hos_num + "<br>Date of birth: " + item.dob + "</a>")
                    .appendTo(ul);
            };
        }
    }

    exports.Search = {
        init: function ($input) {
            $searchInput = $input;
            this.$searchInput = $input;
            initAutocomplete();
        },
        setSourceURL: function(url){
            autocompleteSource = url;
        },

        getElement: function(){
            return this.$searchInput;
        }
  };
}(this.OpenEyes.UI));