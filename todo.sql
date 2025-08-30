drop database if exists todo_app;
create database todo_app;
use todo_app;

create table tags (
    id int auto_increment primary key,
    name varchar(100) not null
);

create table tasks (
    id int auto_increment primary key,
    title varchar(255) not null,
    tag_id int,
    created_at timestamp default current_timestamp,
    foreign key (tag_id) references tags(id)
);

insert into tags (name) values ('Personal'), ('Work'), ('Urgent');

insert into tasks (title, tag_id) values ('Buy groceries', 1), ('Finish project report', 2), ('Call plumber', 1), ('Pay bills', 3);
