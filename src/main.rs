use std::io::{BufRead, BufReader, Write};
use std::net::TcpListener;
use rusqlite::{params, Error};

fn main() -> anyhow::Result<()> {
    init();
    println!("Hello, world!");
    let c = rusqlite::Connection::open("db.sqlite")?;
    c.execute("CREATE TABLE IF NOT EXISTS main_table (`id` integer, `name` varchar(100));", params![])?;
    c.execute("INSERT INTO main_table VALUES(1,\"a\"), (2,\"b\")", params![])?;

    let mut stmt = c.prepare("SELECT `id`, `name` FROM main_table")?;
    let s = stmt.query_map(params![], |row| {
        let k:Result<i64, Error> = row.get(0, );
        Ok(k?)
    })?;
    for row in s {
        println!("{:?}", row);
    }
    Ok(())
}

fn init(){
    let listener = TcpListener::bind("127.0.0.1:8080").unwrap();
    for stream in listener.incoming(){
        let mut stream = stream.unwrap();
        let req:Vec<_> = BufReader::new(&stream)
            .lines()
            .map(|result|result.unwrap())
            .take_while(|x| !x.is_empty())
            .collect();
        println!("{:#?}", req);
        let result = "HTTP/1.1 200 OK\r\n\r\n";
        stream.write(result.as_bytes()).unwrap();
    }
}