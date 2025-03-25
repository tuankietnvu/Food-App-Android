package com.example.myapplication.Domain;

import java.util.Map;

public class Order {
    public int orderId;
    public String userId;
    public Map<String, OrderItem> items;
    public double subtotal;
    public double delivery_fee;
    public double tax;
    public double total_price;
    public Address address;

    public Order() {}

    public Order(int orderId, String userId, Map<String, OrderItem> items, double subtotal, double delivery_fee, double tax, double total_price, Address address) {
        this.userId = userId;
        this.items = items;
        this.subtotal = subtotal;
        this.delivery_fee = delivery_fee;
        this.tax = tax;
        this.total_price = total_price;
        this.address = address;
        this.orderId = orderId;
    }
}