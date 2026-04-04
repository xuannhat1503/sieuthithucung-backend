package com.sieuthithucung.controller;

import com.sieuthithucung.dto.OrderStatusHistoryDto;
import com.sieuthithucung.service.OrderStatusHistoryService;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import java.util.List;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/order-status-history")
public class OrderStatusHistoryController {

    private final OrderStatusHistoryService service;

    public OrderStatusHistoryController(OrderStatusHistoryService service) {
        this.service = service;
    }

    @PostMapping
    public ResponseEntity<OrderStatusHistoryDto> create(@RequestBody OrderStatusHistoryDto dto) {
        return ResponseEntity.status(HttpStatus.CREATED).body(service.create(dto));
    }

    @PutMapping("/{id}")
    public ResponseEntity<OrderStatusHistoryDto> update(@PathVariable Long id, @RequestBody OrderStatusHistoryDto dto) {
        return ResponseEntity.ok(service.update(id, dto));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> delete(@PathVariable Long id) {
        service.delete(id);
        return ResponseEntity.noContent().build();
    }

    @GetMapping("/{id}")
    public ResponseEntity<OrderStatusHistoryDto> findById(@PathVariable Long id) {
        return ResponseEntity.ok(service.findById(id));
    }

    @GetMapping
    public ResponseEntity<List<OrderStatusHistoryDto>> getAll() {
        return ResponseEntity.ok(service.getAll());
    }
}