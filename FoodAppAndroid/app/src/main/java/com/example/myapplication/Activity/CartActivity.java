package com.example.myapplication.Activity;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.example.myapplication.Adapter.CartAdapter;
import com.example.myapplication.Domain.Address;
import com.example.myapplication.Domain.Foods;
import com.example.myapplication.Domain.Order;
import com.example.myapplication.Domain.OrderItem;
import com.example.myapplication.Helper.ChangeNumberItemsListener;
import com.example.myapplication.Helper.ManagmentCart;
import com.example.myapplication.R;
import com.example.myapplication.databinding.ActivityCartBinding;
import com.example.myapplication.databinding.ActivityDetailBinding;
import com.google.firebase.database.DataSnapshot;
import com.google.firebase.database.DatabaseError;
import com.google.firebase.database.DatabaseReference;
import com.google.firebase.database.FirebaseDatabase;
import com.google.firebase.database.ValueEventListener;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

public class CartActivity extends BaseActivity {
    private ActivityCartBinding binding;
    private RecyclerView.Adapter adapter;
    private ManagmentCart managmentCart;
    private double tax;
    FirebaseDatabase database;
    DatabaseReference ordersRef;

    private double total;
    private double itemTotal;


    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityCartBinding.inflate(getLayoutInflater());

        database = FirebaseDatabase.getInstance();
        ordersRef = database.getReference("orders");



        setContentView(binding.getRoot());
        managmentCart = new ManagmentCart(this);



        setVariable();
        calculateCart();
        initList();


        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });
    }

    private void addNewOrder() {
        // Đọc ID lớn nhất hiện tại
        ordersRef.orderByKey().limitToLast(1).addListenerForSingleValueEvent(new ValueEventListener() {
            @Override
            public void onDataChange(@NonNull DataSnapshot snapshot) {
                int newOrderId = 0; // Mặc định là 0 nếu database trống

                if (snapshot.exists()) {
                    for (DataSnapshot child : snapshot.getChildren()) {
                        newOrderId = Integer.parseInt(child.getKey()) + 1; // ID mới = ID lớn nhất + 1
                    }
                }

                // Thêm đơn hàng mới với ID tăng dần
                insertOrder(newOrderId);
            }

            @Override
            public void onCancelled(@NonNull DatabaseError error) {
                Log.e("Firebase", "Failed to read order ID", error.toException());
            }
        });
    }

    private void insertOrder(int orderId) {
        // Lấy danh sách món ăn từ cart
        ArrayList<Foods> cartItems = managmentCart.getListCart();

        // Chuyển đổi danh sách món ăn thành Map<String, OrderItem>
        Map<String, OrderItem> items = new HashMap<>();

        for (int i = 0; i < cartItems.size(); i++) {
            Foods food = cartItems.get(i);

            // Tạo ID cho từng món ăn (item_001, item_002, ...)
            String itemId = "item_" + String.format("%03d", i + 1);

            // Tạo OrderItem từ Foods object
            double itemUnitPrice = food.getPrice();
            int quantity = food.getNumberInCart();
            double itemTotalPrice = itemUnitPrice * quantity;


            // Tạo đối tượng OrderItem và thêm vào Map
            items.put(itemId, new OrderItem(
                    food.getId(),
                    food.getTitle(),
                    quantity,
                    itemUnitPrice,
                    itemTotalPrice
            ));
        }

        Address address = new Address("Nguyen Van A", "0123456789", "123 ABC Street, City");

        Order order = new Order(orderId,"user_001", items, itemTotal, 10, tax, total, address);

        // Lưu đơn hàng vào Firebase với ID tăng dần
        ordersRef.child(String.valueOf(orderId)).setValue(order)
                .addOnSuccessListener(aVoid -> Log.d("Firebase", "Order " + orderId + " added successfully"))
                .addOnFailureListener(e -> Log.e("Firebase", "Failed to add order", e));

        managmentCart.clearCart();

        Toast.makeText(this, "Order successfuly", Toast.LENGTH_SHORT).show();
        // Tạo intent để quay về màn hình chính
        Intent intent = new Intent(CartActivity.this, MainActivity.class);
        intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP); // Xóa các activity ở trên MainActivity trong stack
        startActivity(intent);

        // Đóng activity hiện tại
        finish();
    }

    private void initList() {
        if(managmentCart.getListCart().isEmpty()){
            binding.empTxt.setVisibility(View.VISIBLE);
            binding.scrollViewCart.setVisibility(View.GONE);
        }
        else{
            binding.empTxt.setVisibility(View.GONE);
            binding.scrollViewCart.setVisibility(View.VISIBLE);
        }

        LinearLayoutManager linearLayoutManager = new LinearLayoutManager(this, LinearLayoutManager.VERTICAL, false);
        binding.cartView.setLayoutManager(linearLayoutManager);
        adapter = new CartAdapter(managmentCart.getListCart(), this, () -> calculateCart());

        binding.cartView.setAdapter(adapter);
    }


    private void calculateCart() {
        double percentTax = 0.02;//percent
        double delivery = 10; //dollar

        tax = Math.round(managmentCart.getTotalFee() * percentTax * 100.0) / 100;

         total = Math.round((managmentCart.getTotalFee() + tax + delivery) * 100) / 100;
         itemTotal = Math.round(managmentCart.getTotalFee() * 100) / 100;

        binding.totalFeeTxt.setText("$" + itemTotal);
        binding.taxFeeTxt.setText("$" + tax);
        binding.deliveryFeeTxt.setText("$" + delivery);
        binding.totalTxt.setText("$" + total);
    }

    private void setVariable() {
        binding.backBtn.setOnClickListener(view -> finish());

        binding.btnOrder.setOnClickListener(v->{
            addNewOrder();
        });
    }
}