LOAD DATA INFILE '/tmp/dates.txt' REPLACE INTO TABLE change_tracker  
FIELDS TERMINATED BY '|' (@id, @first_indexed) 
SET 
id=concat('(Sirsi) a', @id),
core='biblio',
first_indexed=@first_indexed, 
last_indexed=@first_indexed,
last_record_change=@first_indexed;
