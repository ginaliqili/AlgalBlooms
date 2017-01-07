
import psycopg2

try:
    conn = psycopg2.connect("dbname='pjm' user='climaton_postgres_user' host='52.11.85.142' password='darwin0508'")
except:
    print('I am unable to connect to the database');

cur = conn.cursor();

try:
    cur.execute("""SELECT state_abbr from algal_blooms.lakesandstates_reprojected""")
except:
    print('I cannot SELECT from algal_blooms.lakesandstates_reprojected');

rows = cur.fetchall()
print('\nRows: \n');
for row in rows:
    if type(row[0]) is str:
        print('   ' + row[0]);
        get_current_lake_count_query = "select lake_count from algal_blooms.us_states where stusps='" + row[0] + "'";
        print(get_current_lake_count_query);
        cur.execute(get_current_lake_count_query);
        conn.commit();
        row2 = cur.fetchone();
        #print(str(row2[0]));
        update_lake_count_query = "update algal_blooms.us_states set lake_count=" + str(row2[0] + 1) + " where stusps='" + row[0] + "'";
        try:
            cur.execute(update_lake_count_query);
        except:
            print('I cannot execute update_lake_count_query');
        print(str(row2[0]));
        print(update_lake_count_query);
