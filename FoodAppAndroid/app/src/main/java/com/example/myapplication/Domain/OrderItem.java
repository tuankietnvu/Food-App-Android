package com.example.myapplication.Domain;

public class OrderItem {
    public int id;
    public String name;
    public int quantity;
    public double price;
    public double total;

    public OrderItem() {}

    public OrderItem(int id, String name, int quantity, double price, double total) {
        this.name = name;
        this.quantity = quantity;
        this.price = price;
        this.total = total;
        this.id = id;
    }
}