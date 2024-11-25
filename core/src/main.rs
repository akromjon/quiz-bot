mod config;
mod model;
use config::app;
use model::database;
fn main() {
    let _ = database::run_db_connection_in_memory();
}
