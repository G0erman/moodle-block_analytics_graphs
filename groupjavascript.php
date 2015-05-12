<?php
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

?>
<!--DOCTYPE HTML-->
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title><?php echo get_string('submissions', 'block_analytics_graphs'); ?></title>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">        
        <script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.11.1.js"></script>        
        <script src="http://code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
        <script src="http://code.highcharts.com/highcharts.js"></script>
        <script src="http://code.highcharts.com/modules/no-data-to-display.js"></script>
        <script src="http://code.highcharts.com/modules/exporting.js"></script> 
        <script type="text/javascript">
            var courseid = <?php echo json_encode($submissions_graph->get_course()); ?>;
            
            var codename = <?php echo json_encode($codename); ?>;           

            var groups = <?php echo $groupmembers_json; ?>;
            var result_json = <?php echo $result_json; ?>;
            var students_json = <?php echo $students_json; ?>;
            var geral = <?php echo $statistics_json; ?>;
            
            //full students on groups
            $.each(groups, function(index, group){
                group.assign = [];
                group.duedate = [];
                
                group.in_time_submissions = [];
                group.latesubmissions = [];
                group.no_submissions = [];

                group.numberofintimesubmissions = [];
                group.numberoflatesubmissions = [];
                group.numberofnosubmissions = [];

                group.submission_ratio = [];
                group.in_time_ratio = [];
            });
            console.log(geral);

            $.each(geral, function(index, value){
                
                $.each(groups, function(ind, group){
                    if(group.assign[index] === undefined)
                        group.assign[index] = value.assign;
        
                    if(group.duedate[index] === undefined)
                        group.duedate[index] = value.duedate;

                    //foreach serie
                    if (value.numberofintimesubmissions > 0){
                        $.each(value.in_time_submissions, function(i, student){
                            if(group.in_time_submissions[index] === undefined)
                                group.in_time_submissions[index] = [];

                            if(group.numberofintimesubmissions[index] === undefined)
                                group.numberofintimesubmissions[index] = 0;

                            if(group.members.indexOf(student.userid) != -1){
                                group.in_time_submissions[index].push(value.in_time_submissions[i]);
                                group.numberofintimesubmissions[index] += 1;
                            }
                        });
                    }else{
                        if(group.numberofintimesubmissions[index] === undefined)
                            group.numberofintimesubmissions[index] = 0;

                        if(group.in_time_submissions[index] === undefined)
                            group.in_time_submissions[index] = [];
                    }

                    if (value.numberoflatesubmissions > 0){
                        $.each(value.latesubmissions, function(i, student){
                            if(group.latesubmissions[index] === undefined)
                                group.latesubmissions[index] = [];

                            if(group.numberoflatesubmissions[index] === undefined)
                                group.numberoflatesubmissions[index] = 0;

                            if(group.members.indexOf(student.userid) != -1){
                                group.latesubmissions[index].push(value.latesubmissions[i]);
                                group.numberoflatesubmissions[index] += 1;
                            }
                        });
                    }else{
                        if(group.numberoflatesubmissions[index] === undefined)
                            group.numberoflatesubmissions[index] = 0;

                        if(group.latesubmissions[index] === undefined)
                            group.latesubmissions[index] = [];
                    }

                    if (value.numberofnosubmissions > 0){
                        $.each(value.no_submissions, function(i, student){
                            if(group.no_submissions[index] === undefined)
                                group.no_submissions[index] = [];

                            if(group.numberofnosubmissions[index] === undefined)
                                group.numberofnosubmissions[index] = 0;

                            if(group.members.indexOf(student.userid) != -1){
                                group.no_submissions[index].push(value.no_submissions[i]);
                                group.numberofnosubmissions[index] += 1;
                            }
                        });
                    }else{
                        if(group.numberofnosubmissions[index] === undefined)
                            group.numberofnosubmissions[index] = 0;

                        if(group.no_submissions[index] === undefined)
                            group.no_submissions[index] = [];
                    }
                });
            });
            //after final values calculate the ratio
            $.each(geral, function(index, value) {
                $.each(groups, function(ind, group){

                    var time = new Date().getTime();

                    group.submission_ratio[index] = parseFloat(parseFloat((group.numberofintimesubmissions[index] + group.numberoflatesubmissions[index]) /
                                (group.numberofintimesubmissions[index] + group.numberoflatesubmissions[index] + group.numberofnosubmissions[index])).toFixed(2));

                    if(group.duedate[index] == 0  || group.duedate[index] > time){
                        group.in_time_ratio[index] = 1;
                    }else{
                        group.in_time_ratio[index] = parseFloat(parseFloat(group.numberofintimesubmissions[index] /
                            (group.numberofintimesubmissions[index] + group.numberoflatesubmissions[index] + group.numberofnosubmissions[index])).toFixed(2));
                    }
                });
            });
            var course = '<?php echo $course; ?>';
            var title_php = "<?php echo $title; ?>";
        </script>
    </head>
    <body>
        <?php if(sizeof($groupmembers)>0){ ?>
        <div style="margin: 20px;">
            <select id="group_select">
                <option value="-"><?php  echo json_encode(get_string('all_groups', 'block_analytics_graphs'));?></option>
                <?php foreach ($groupmembers as $key => $value) { ?>
                    <option value="<?php echo $key; ?>"><?php echo $value["name"]; ?></option>
                <?php } ?>
            </select>
        </div>
        <?php } ?>
        <div id="container" style="min-width: 310px; min-width: 800px; height: 650px; margin: 0 auto"></div>
        <script>
            $(function(){
                var groups = <?php echo $groupmembers_json; ?>;
                $('#container').highcharts(<?php echo $submissions_graph_options; ?>);
            })
            geral = parseObjToString(geral);
            $.each(geral, function(index, value) {
                var nome = value.assign;
                div = "";
                if (typeof value.in_time_submissions != 'undefined')
                {
                    title = <?php echo json_encode($submissions_graph->get_coursename()); ?> +
                        "</h3>" + 
                        <?php echo json_encode(get_string('in_time_submission', 'block_analytics_graphs')); ?> +
                        " - " +  nome ;
                    div += "<div class='div_nomes' id='" + index + "-0'>" + 
                        createEmailForm(title, value.in_time_submissions, courseid, codename) +
                        "</div>";
                }
                if (typeof value.latesubmissions != 'undefined')
                {
                    title = <?php echo json_encode($submissions_graph->get_coursename()); ?> +
                        "</h3>" +
                        <?php echo json_encode(get_string('late_submission', 'block_analytics_graphs')); ?> +
                        " - " +  nome ;
                    div += "<div class='div_nomes' id='" + index + "-1'>" +
                        createEmailForm(title, value.latesubmissions, courseid, codename) +
                        "</div>";
                }
                if (typeof value.no_submissions != 'undefined')
                {
                    title = <?php echo json_encode($submissions_graph->get_coursename()); ?> +
                        "</h3>" + 
                        <?php echo json_encode(get_string('no_submission', 'block_analytics_graphs')); ?> +
                        " - " +  nome ;
                    div += "<div class='div_nomes' id='" + index + "-2'>" +
                        createEmailForm(title, value.no_submissions, courseid, codename) +
                        "</div>";
                }
                document.write(div);

                //groups forms
                $.each(groups, function(ind, group) {
                    //var nome = group.assign;
                    div = "";

                    if (typeof group.in_time_submissions[index] != 'undefined')
                    {
                        title = <?php echo json_encode($submissions_graph->get_coursename()); ?> +
                            "</h3>" + 
                            <?php echo json_encode(get_string('in_time_submission', 'block_analytics_graphs')); ?> +
                            " - " +  nome ;
                        div += "<div class='div_nomes' id='" + index + "-0-"+ind+"'>" + 
                            createEmailForm(title, group.in_time_submissions[index], courseid, codename) +
                            "</div>";
                    }
                    if (typeof group.latesubmissions[index] != 'undefined')
                    {
                        title = <?php echo json_encode($submissions_graph->get_coursename()); ?> +
                            "</h3>" +
                            <?php echo json_encode(get_string('late_submission', 'block_analytics_graphs')); ?> +
                            " - " +  nome ;
                        div += "<div class='div_nomes' id='" + index + "-1-"+ind+"'>" +
                            createEmailForm(title, group.latesubmissions[index], courseid, codename) +
                            "</div>";
                    }
                    if (typeof group.no_submissions[index] != 'undefined')
                    {
                        title = <?php echo json_encode($submissions_graph->get_coursename()); ?> +
                            "</h3>" + 
                            <?php echo json_encode(get_string('no_submission', 'block_analytics_graphs')); ?> +
                            " - " +  nome ;
                        div += "<div class='div_nomes' id='" + index + "-2-"+ind+"'>" +
                            createEmailForm(title, group.no_submissions[index], courseid, codename) +
                            "</div>";
                    }
                    document.write(div);
                });
            });

            //enable form to send email
            sendEmail();

            $( "#group_select" ).change(function() {
                //reset series data
                $("#container").highcharts().series[0].setData([0]);
                $("#container").highcharts().series[1].setData([0]);
                $("#container").highcharts().series[2].setData([0]);
                $("#container").highcharts().series[3].setData([0]);
                $("#container").highcharts().series[4].setData([0]);

                var group = $(this).val();
                if(group != "-"){

                    $.each(groups, function(index, value){
                        if(group == index){
                            //update series data
                            $("#container").highcharts().series[0].setData(value.numberofintimesubmissions);
                            $("#container").highcharts().series[1].setData(value.numberoflatesubmissions);
                            $("#container").highcharts().series[2].setData(value.numberofnosubmissions);
                            $("#container").highcharts().series[3].setData(value.submission_ratio);
                            $("#container").highcharts().series[4].setData(value.in_time_ratio);
                        }
                    });
                }else{
                    var numberofintimesubmissions = [];
                    var numberoflatesubmissions = [];
                    var numberofnosubmissions = [];
                    var submission_ratio = [];
                    var in_time_ratio = [];
                    $.each(geral, function(index, value){

                        var time = new Date().getTime();
                        var submission_ratio_value = parseFloat(parseFloat((value.numberofintimesubmissions + value.numberoflatesubmissions) /
                                    (value.numberofintimesubmissions + value.numberoflatesubmissions + value.numberofnosubmissions)).toFixed(2));

                        if(value.duedate == 0 || value.duedate > time){
                            var in_time_ratio_value = 1;
                        }else{
                            var in_time_ratio_value = parseFloat(parseFloat(value.numberofintimesubmissions /
                                (value.numberofintimesubmissions + value.numberoflatesubmissions + value.numberofnosubmissions)).toFixed(2));
                        }
                        numberofintimesubmissions.push(value.numberofintimesubmissions);
                        numberoflatesubmissions.push(value.numberoflatesubmissions);
                        numberofnosubmissions.push(value.numberofnosubmissions);
                        submission_ratio.push(submission_ratio_value);
                        in_time_ratio.push(in_time_ratio_value);
                    });
                    //update series data
                    $("#container").highcharts().series[0].setData(numberofintimesubmissions);
                    $("#container").highcharts().series[1].setData(numberoflatesubmissions);
                    $("#container").highcharts().series[2].setData(numberofnosubmissions);
                    $("#container").highcharts().series[3].setData(submission_ratio);
                    $("#container").highcharts().series[4].setData(in_time_ratio);
                }
            });
        </script>
    </body>
</html>