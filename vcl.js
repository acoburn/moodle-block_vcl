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

(function(Y, M){
    M.block_vcl = {};

    M.block_vcl.openAlternatives = [];

    M.block_vcl.show_alternatives = function(resid){
        var e = Y.one("#connAlt_"+resid),
            exists = 0,
            i = 0,
            newArray = [];
        if(e.hasClass('inactive')){
            for(i=0; i<M.block_vcl.openAlternatives.length; i += 1){
                if (M.block_vcl.openAlternatives[i] == resid){
                    exists = 1;
                }
            }
            if(!exists){
                M.block_vcl.openAlternatives.push(resid);
            }
        } else {
            for(i=0; i<M.block_vcl.openAlternatives.length; i += 1){
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
        var e = window.document.getElementById("vcl_block_screen_"+requestid),
            screen = e.options[e.selectedIndex].value,
            dim = screen.split(/x/i);
        if(screen == "fullscreen"){
            params += "&fullscreen=yes";
        } else {
            if(dim.length == 2){
                params += "&screenWidth=" + dim[0] + "&screenHeight=" + dim[1];
            }
        }
        window.location = "rdp://" + username + ":" + password + "@" + server + "?" + params;
    };

    M.block_vcl.remove = function(id){
        Y.one("#vclCurrent").set('innerHTML', '<img src="../blocks/vcl/pix/throbber.gif" class="throbber" />');
        Y.io(M.cfg.wwwroot + "/blocks/vcl/ajax.php?action=deleteReservation&id="+id, {
            on: { complete: function(){
                M.block_vcl.reservations();
            }}
        });
    };

    M.block_vcl.extend = function(id){
        Y.one("#vclCurrent").set('innerHTML', '<img src="../blocks/vcl/pix/throbber.gif" class="throbber" />');
        Y.io(M.cfg.wwwroot + "/blocks/vcl/ajax.php?action=extendReservation&id="+id, {
            on: { complete: function(){
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
        Y.io(M.cfg.wwwroot + "/blocks/vcl/ajax.php?action=getReservations", {
            on: { complete: function(id, o){
                var json = Y.JSON.parse(o.responseText),
                    timer = 0,
                    html = '',
                    item = null,
                    now = new Date(),
                    selected = {},
                    ids = M.block_vcl.openAlternatives,
                    i = 0;
                if (json.error){
                    Y.one("#vclCurrent").set('innerHTML', json.error);
                } else {
                    for (item in json){
                        if(json.hasOwnProperty(item)){
                            html += json[item].html;
                            if (json[item].status == "loading"){
                                timer = 5;
                            } else if (json[item].status == "ready" && !timer){
                                timer = 30;
                            } else if (Math.abs(json[item].start - now.getTime() / 1000) < 60 * 10 && !timer){
                                timer = 30;
                            }
                        }
                    }
                    if(Y.one('#vclCurrent').get('innerHTML') != html){
                        Y.one('#vclCurrent').all("select").each(function(){
                            selected[this.get('id')] = this.get('value');
                        });
                        Y.one('#vclCurrent').set('innerHTML', html);
                        for (item in selected){
                            if (selected.hasOwnProperty(item)){
                              Y.one("#" + item).set('value', selected[item]);
                            }
                        }
                    }
                    for (i=0; i < ids.length; i += 1){
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
        Y.io(M.cfg.wwwroot + "/blocks/vcl/ajax.php?action=getImages", {
            on: { complete: function(id, o){
                var json = Y.JSON.parse(o.responseText),
                    index = null,
                    html = "";
                if (json.error){
                    Y.one("#vclNew").set('innerHTML', json.error);
                } else {
                    for (index in json){
                        if (json.hasOwnProperty(index)){
                            html += "<option value=\"" + json[index].id + "\">" + json[index].name + "</option>\n";
                        }
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
        var today = new Date(),
            later = new Date(today.getTime() + 1000*60*60*24*4),
            hourScale = 5,
            sliderHour = Y.YUI2.widget.Slider.getHorizSlider("vclTimeContainerHour",
                         "slider-thumb-hour", 0, 120, 5),
            minuteScale = 2,
            sliderMinute = Y.YUI2.widget.Slider.getHorizSlider("vclTimeContainerMinute",
                         "slider-thumb-minute", 0, 120, 30),
            calendar = new Y.YUI2.widget.Calendar('cal', 'vclCalContainer',
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

        sliderHour.animate = true;
        sliderHour.getRealValue = function() {
            return Math.round(this.getValue() / hourScale);
        };
        sliderHour.subscribe("change", function() {
            var fld = Y.one("#vclTime"),
                actualValue = parseInt(this.getRealValue(), 10),
                matches = null,
                minute = "00";
            // update the text box with the actual value
            if (fld.get('value')){
                matches = fld.get('value').match(/\d{1,2}:(\d{2}) [ap]m/i);
                if (matches){
                    minute = matches[1];
                }
            }
            fld.set('value', (actualValue % 12 || "12") + ":" + minute + " " + (actualValue % 24 >= 12 ? "pm" : "am"));
        }, sliderHour, true);

        sliderMinute.animate = true;
        sliderMinute.getRealValue = function() {
            return Math.round(this.getValue() / minuteScale);
        };

        sliderMinute.subscribe("change", function() {
            var fld = Y.one("#vclTime"),
                actualValue = parseInt(this.getRealValue(), 10),
                matches = null,
                hour = "12", ampm = "am";
            // update the text box with the actual value
            if (fld.get('value')){
                matches = fld.get('value').match(/(\d{1,2}):\d{2} ([ap]m)/i);
                if (matches){
                    hour = matches[1];
                    ampm = matches[2];
                }
            }
            fld.set('value', hour + ":" + (actualValue % 60 || "00") + " " + ampm);
        }, sliderMinute, true);

        // Setup event handlers
        Y.one('#vclDate').on('click', function(e){
            e.halt(); // don't propogate events.
            Y.one("#vclTimeContainer").setStyle('display', 'none');
            var date = e.target.get('value'),
                handleCal = Y.one("#vclCalContainer").on('click', function(e){
                    e.halt(); // also don't propogate click events here.
                }),
                handleDoc = Y.one(window.document).on('click', function(){
                    calendar.hide();
                    handleDoc.detach();
                    handleCal.detach();
                });
            calendar.cfg.setProperty('selected', date || '');
            calendar.cfg.setProperty('pagedate', date ? new Date(date) : new Date(), true);
            calendar.render();
            Y.one("#vclCalContainer").setStyle('display', 'block');
        });

        Y.one('#vclTime').on('click', function(e){
            e.halt(); // don't propogate events.
            calendar.hide();
            Y.one("#vclTimeContainer").setStyle('display', 'block');
            var matches = e.target.get('value').match(/^(\d{1,2}):(\d{2})\s+([ap]m)/i),
                hour = null,
                minute = null,
                handleTime = Y.one("#vclTimeContainer").on('click', function(e){
                    e.halt();
                }),
                handleDoc = Y.one(window.document).on('click', function(){
                    Y.one("#vclTimeContainer").setStyle('display', 'none');
                    handleDoc.detach();
                    handleTime.detach();
                });
            if (matches){
                hour = matches[3].toLowerCase() == "pm" ? parseInt(matches[1], 10) % 12 + 12 : parseInt(matches[1], 10) % 12;
                minute = parseInt(matches[2], 10) % 60;
                sliderHour.setValue(hour * hourScale, true, true, true);
                sliderMinute.setValue(minute * minuteScale, true, true, true);
            } else {
                sliderHour.setValue(0, true, true, true);
                sliderMinute.setValue(0, true, true, true);
                e.target.set('value',"");
            }
        });

        Y.one('#vclWhen').on("change", function(e){
            if (e.target.get('value') == "Now"){
                Y.one("#vclDateSelector").setStyle('display', 'none');
                calendar.hide();
            } else {
                Y.one("#vclDateSelector").setStyle('display', 'block');
            }
        });

        Y.one('#vclButton').on("click", function(){
            Y.one("#vclCurrent").set('innerHTML', '<img src="../blocks/vcl/pix/throbber.gif" class="throbber" />');
            Y.io(M.cfg.wwwroot + "/blocks/vcl/ajax.php", {
                method: 'POST',
                form: { id: 'vclReservationForm' },
                on: { complete: function(){
                    Y.one("#vclTime").set('value',"");
                    M.block_vcl.reservations();
                }}
            });
            Y.one("#vclDateSelector").setStyle('display','none');
            M.block_vcl.reset();
        });
    };
})(Y, M);
