use rusqlite::{Connection, Result, Statement};

pub trait Model: Sized {
    fn get(id: u32) -> Option<Self>;
    fn all() -> Vec<Self>;
    fn find(condition: &str) -> Option<Self>;
    fn create(data: &str) -> Result<Self, String>;
    fn update(&mut self, data: &str) -> Result<(), String>;
    fn delete(self) -> Result<bool, String>;
}




pub fn run_db_connection_in_memory() -> Result<()> {
    let conn = Connection::open("../database/database.sqlite")?;

    let mut stmt = conn.prepare("SELECT id,user_id FROM telegram_users")?;
    let rows = stmt.query_map([], |row| {
        let id: i32 = row.get(0)?;  // Get the first column as i32
        let user_id: i64 = row.get(1)?;  // Get the second column as String
        Ok((id, user_id))  // Return a tuple of values
    })?;

    // Iterate through the results and print them
    for result in rows {
        match result {
            Ok((id, user_id)) => println!("User ID: {}, user_id: {}", id, user_id),
            Err(e) => eprintln!("Error processing row: {e}"),
        }
    }

    Ok(())
}
