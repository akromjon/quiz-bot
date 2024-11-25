use dotenv;
use std::env;

#[derive(Debug)]
pub struct Config {
    db_connection: Option<String>,
    redis_client: Option<String>,
    redis_host: Option<String>,
    redis_password: Option<String>,
    redis_port: Option<String>,
}

impl Config {
    pub fn load() -> Config {
        return Config {
            db_connection: get("DB_CONNECTION"),
            redis_client: get("REDIS_CLIENT"),
            redis_host: get("REDIS_HOST"),
            redis_password: get("REDIS_PASSWORD"),
            redis_port: get("REDIS_PORT"),
        };
    }
}

pub fn load() -> Config {
    dotenv::from_filename(".env")
        .ok()
        .expect(".env is does not exist?");

    return Config::load();
}

fn get<K: AsRef<str>>(key: K) -> Option<String> {
    return match env::var(key.as_ref()) {
        Ok(v) => Some(v),
        Err(_) => None,
    };
}
