# Python utils

import re

def format_query(sql, db_prefix, params=None):   
    """
    Add prefix to a table and make the query compatible with mysql databases
    
    sql: Query to transform.
    db_prefix: Prefix use in your database motor
    params: require the same number of parameters present in the query
    """
    
    def replace_string_by_index(string, new):
        index = string.find('?')
        before = string[:index]
        after = string[index+1:]
        return before+'@'+new+after
    
    # save word inside { }, and add db_prefix
    sql = re.sub(r'{(\w+)}',db_prefix+r'\1 AS', sql)

    # Replace {" . $course .", " . $course . ",} by @course
    sql = re.sub(r'\" \. \$(\w+)( \.( )*\")*',r'@\1',sql)
    
    if params != None:
        params = params.split()
        for param in params:
            param = param.replace('$','').replace(',','')
            #print(param)
            sql = replace_string_by_index(sql,param)
    
    print(sql)

# Test
query2 = """
SELECT b.id discussionid, b.name discussionname
            FROM {forum} a
            LEFT JOIN {forum_discussions} b on a.id = b.forum
            LEFT JOIN {forum_posts} c on b.id = c.discussion
            WHERE a.course = " . $course ." AND c.userid = " . $student
"""

query3 = """
SELECT userid+(week*1000000), userid, firstname, lastname, email, week, number
                FROM (
                    SELECT  userid, week, COUNT(*) as number
                    FROM (
                        SELECT log.userid, module, cmid,
                        FLOOR((((log.time + ?) / 86400) - (?/86400))/7) as week
                        FROM {log} log
                        WHERE course = ? AND (action = 'view' OR action = action = 'view forum')
                            AND module <> 'assign' AND cmid <> 0 AND time >= ? AND log.userid $insql
                        GROUP BY userid, week, module, cmid
                    ) as temp
                    GROUP BY userid, week
                ) as temp2
                LEFT JOIN {user} usr ON usr.id = temp2.userid
                ORDER by LOWER(firstname), LOWER(lastname), userid, week
"""

format_query(query2,'prefix_')

params = "$timezoneadjust, $startdate, $course, $startdate"
format_query(query3,'prefix_',params)