package com.example.myapplication.Domain;

public class Time {
    private int Id;
    private String Value;

    public Time() {
    }

    @Override
    public String toString() {
        return Value;
    }

    public String getValue() {
        return Value;
    }

    public void setValue(String value) {
        Value = value;
    }

    public int getId() {
        return Id;
    }

    public void setId(int id) {
        Id = id;
    }
}
