// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * This Moodle block can be used to access the Virtual Computing Lab.
 * For more information about the VCL, visit http://vcl.apache.org
 * 
 * @package    blocks
 * @subpackage vcl
 * @author     Aaron Coburn <acoburn@amherst.edu>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @copyright  (C) 2012 Amherst College
 *
 * This Moodle plugin provides access to a Virtual Computing Lab
 * infrastructure. It allows users to make and manage reservations
 * to remote computing environments.
 */

YUI('vcl_block').use('node', 'io', 'io-base', 'json-parse', function (Y){

    M.block_vcl = {};

    M.block_vcl.openAlternatives = [];

    M.block_vcl.show_alternatives = function(resid){
        var e = Y.one("#connAlt_"+resid);
        if(e.hasClass('inactive')){
            var exists = 0;
            for(var i=0; i<M.block_vcl.openAlternatives.length; ++i){
                if (M.block_vcl.openAlternatives[i] == resid){
                    exists = 1;
                }
            }
            if(!exists){
                M.block_vcl.openAlternatives.push(resid);
            }
        } else {
            var newArray = [];
            for(var i=0; i<M.block_vcl.openAlternatives.length; ++i){
                if (M.block_vcl.openAlternatives[i] != resid){
                    newArray.push(M.block_vcl.openAlternatives[i]);
                }
            }
            M.block_vcl.openAlternatives = newArray;
        }
        e.toggleClass('active');
        e.toggleClass('inactive');
        e.ancestor('p').next('div').toggleClass('alt_connection');
    };

    M.block_vcl.connect = function(username, password, server, requestid, params){
        var e = document.getElementById("vcl_block_screen_"+requestid);
        var screen = e.options[e.selectedIndex].value;
        if(screen == "fullscreen"){
            params += "&fullscreen=yes";
        } else {
            var dim = screen.split(/x/i);
            if(dim.length == 2){
                params += "&screenWidth=" + dim[0] + "&screenHeight=" + dim[1];
            }
        }
        window.location = "rdp://" + username + ":" + password + "@" + server + "?" + params;
    };

    M.block_vcl.delete = function(id){
        Y.one("#vclCurrent").set('innerHTML', '<img src="../blocks/vcl/images/throbber.gif" class="throbber" />');
        Y.io("/blocks/vcl/ajax.php?action=deleteReservation&id="+id, {
            on: { complete: function(id, o){
                M.block_vcl.reservations();
            }}
        });
    };

    M.block_vcl.extend = function(id){
        Y.one("#vclCurrent").set('innerHTML', '<img src="../blocks/vcl/images/throbber.gif" class="throbber" />');
        Y.io("/blocks/vcl/ajax.php?action=extendReservation&id="+id, {
            on: { complete: function(id, o){
                M.block_vcl.reservations();
            }}
        });
    };

    M.block_vcl.reset = function(){
        Y.one("#vclDate").set('value', '');
        Y.one("#vclTime").set('value', '');
        Y.one("#vclWhen").set('value', 'Now');
    };

    M.block_vcl.reservations = function(){
        // Get a list of current reservations
        Y.io("/blocks/vcl/ajax.php?action=getReservations", {
            on: { complete: function(id, o){
                var json = Y.JSON.parse(o.responseText);
                var timer = 0;
                if (json.error){
                    Y.one("#vclCurrent").set('innerHTML', json.error);
                } else {
                    var html = "";
                    var now = new Date();
                    for (var index in json){
                        html += json[index].html;
                        if (json[index].status == "loading"){
                            timer = 5;
                        } else if (json[index].status == "ready" && !timer){
                            timer = 30;
                        } else if (Math.abs(json[index].start - now.getTime() / 1000) < 60 * 10 && !timer){
                            timer = 30;
                        }
                    }
                    var current = Y.one("#vclCurrent");
                    if(current.get('innerHTML') != html){
                        var selected = Array();
                        current.all("select").each(function(){
                            selected[this.get('id')] = this.get('value');
                        });
                        current.set('innerHTML', html);
                        for(var id in selected){
                            Y.one("#" + id).set('value', selected[id]);
                        }
                    }
                    var ids = M.block_vcl.openAlternatives;
                    for(var i=0; i < ids.length; ++i){
                        M.block_vcl.show_alternatives(ids[i]);
                    }
                    if (timer){
                        setTimeout(M.block_vcl.reservations, 1000 * timer);
                    }
                }
            }}
        });
    };

    M.block_vcl.init = function(Y){
        // Restore field values to default
        M.block_vcl.reset();

        // Get a list of VCL images
        Y.io("/blocks/vcl/ajax.php?action=getImages", {
            on: { complete: function(id, o){
                var json = Y.JSON.parse(o.responseText);
                if (json.error){
                    Y.one("#vclNew").set('innerHTML', json.error);
                } else {
                    var html = "";
                    for (var index in json){
                        html += "<option value=\"" + json[index].id + "\">" + json[index].name + "</option>\n";
                    }
                    Y.one("#vclImageList").set('innerHTML', html);
                }
                Y.one("#vclScheduleReservation > img").setStyle('display','none');
                Y.one("#vclNew").setStyle('display', 'block');
            }}
        });

        // Get a list of current VCL reservations
        M.block_vcl.reservations();

        // Initialize calendar
        var today = new Date();
        var later = new Date(today.getTime() + 1000*60*60*24*4);
        var calendar = new YAHOO.widget.Calendar('cal', 'vclCalContainer',
                            { mindate: today,
                              maxdate: later,
                              hide_blank_weeks: true });
        calendar.selectEvent.subscribe(function(){
            var calDate = calendar.getSelectedDates()[0];
            calDate = (calDate.getMonth() + 1) + '/' + calDate.getDate() + '/' + calDate.getFullYear();
            Y.one("#vclDate").set('value', calDate);
            calendar.hide();
        }, calendar, true);
        calendar.render();

        // Initialize hour slider
        var hourScale = 5;
        var sliderHour = YAHOO.widget.Slider.getHorizSlider("vclTimeContainerHour",
                         "slider-thumb-hour", 0, 120, 5);
        sliderHour.animate = true;
        sliderHour.getRealValue = function() {
            return Math.round(this.getValue() / hourScale);
        }
        sliderHour.subscribe("change", function(offsetFromStart) {
            var fld = Y.one("#vclTime");
            var actualValue = parseInt(sliderHour.getRealValue());
            // update the text box with the actual value
            var minute = "00";
            if (fld.get('value')){
                var matches = fld.get('value').match(/\d{1,2}:(\d{2}) [ap]m/i);
                if (matches){
                    minute = matches[1];
                }
            }
            fld.set('value', (actualValue % 12 ? actualValue % 12 : "12") + ":" + minute + " " + (actualValue % 24 >= 12 ? "pm" : "am"));
        });

        // Initialize minute slider
        var minuteScale = 2;
        var sliderMinute = YAHOO.widget.Slider.getHorizSlider("vclTimeContainerMinute",
                         "slider-thumb-minute", 0, 120, 30);
        sliderMinute.animate = true;
        sliderMinute.getRealValue = function() {
            return Math.round(this.getValue() / minuteScale);
        }

        sliderMinute.subscribe("change", function(offsetFromStart) {
            var fld = Y.one("#vclTime");
            var actualValue = parseInt(sliderMinute.getRealValue());
            // update the text box with the actual value
            var hour = "12", ampm = "am";
            if (fld.get('value')){
                var matches = fld.get('value').match(/(\d{1,2}):\d{2} ([ap]m)/i);
                if (matches){
                    hour = matches[1];
                    ampm = matches[2];
                }
            }
            fld.set('value', hour + ":" + (actualValue % 60 ? actualValue % 60 : "00") + " " + ampm);
        });

        // Setup event handlers
        Y.one('#vclDate').on('click', function(e){
            e.halt(); // don't propogate events.
            Y.one("#vclTimeContainer").setStyle('display', 'none');
            var date = e.target.get('value');
            calendar.cfg.setProperty('selected', date ? date : '');
            calendar.cfg.setProperty('pagedate', date ? new Date(date) : new Date(), true);
            calendar.render();
            Y.one("#vclCalContainer").setStyle('display', 'block');
            var handleCal = Y.one("#vclCalContainer").on('click', function(e){
                e.halt(); // also don't propogate click events here.
            });
            var handleDoc = Y.one(document).on('click', function(e){
                calendar.hide();
                handleDoc.detach();
                handleCal.detach();
            });
        });

        Y.one('#vclTime').on('click', function(e){
            e.halt(); // don't propogate events.
            calendar.hide();
            Y.one("#vclTimeContainer").setStyle('display', 'block');
            var matches = e.target.get('value').match(/^(\d{1,2}):(\d{2})\s+([ap]m)/i);
            if (matches){
                var hour = matches[3].toLowerCase() == "pm" ? parseInt(matches[1]) % 12 + 12 : parseInt(matches[1]) % 12;
                var minute = parseInt(matches[2]) % 60;
                sliderHour.setValue(hour * hourScale, true, true, true);
                sliderMinute.setValue(minute * minuteScale, true, true, true);
            } else {
                sliderHour.setValue(0, true, true, true);
                sliderMinute.setValue(0, true, true, true);
                e.target.set('value',"");
            }
            var handleTime = Y.one("#vclTimeContainer").on('click', function(e){
                e.halt();
            });
            var handleDoc = Y.one(document).on('click', function(e){
                Y.one("#vclTimeContainer").setStyle('display', 'none');
                handleDoc.detach();
                handleTime.detach();
            });
        });

        Y.one('#vclWhen').on("change", function(e){
            if (e.target.get('value') == "Now"){
                Y.one("#vclDateSelector").setStyle('display', 'none');
                calendar.hide();
            } else {
                Y.one("#vclDateSelector").setStyle('display', 'block');
            }
        });

        Y.one('#vclButton').on("click", function(e){
            Y.one("#vclCurrent").set('innerHTML', '<img src="../blocks/vcl/images/throbber.gif" class="throbber" />');
            Y.io("/blocks/vcl/ajax.php", {
                method: 'POST',
                form: { id: 'vclReservationForm' },
                on: { complete: function(id, o){
                    Y.one("#vclTime").set('value',"");
                    M.block_vcl.reservations();
                }}
            });
            Y.one("#vclDateSelector").setStyle('display','none');
            M.block_vcl.reset();
        });
    };
});
