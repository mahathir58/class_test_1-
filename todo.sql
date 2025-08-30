-- Step 1: Create database
drop database if exists todo_app;
create database todo_app;
use todo_app;

-- Step 2: Create tags table
create table tags (
    id int auto_increment primary key,
    name varchar(100) not null
);

-- Step 3: Create tasks table
create table tasks (
    id int auto_increment primary key,
    title varchar(255) not null,
    tag_id int,
    created_at timestamp default current_timestamp,
    foreign key (tag_id) references tags(id)
);

-- Step 4: Insert sample tags
insert into tags (name) values ('Personal'), ('Work'), ('Urgent');

-- Step 5: Insert sample tasks
insert into tasks (title, tag_id) values ('Buy groceries', 1), ('Finish project report', 2), ('Call plumber', 1), ('Pay bills', 3);
