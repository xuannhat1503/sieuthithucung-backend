package com.sieuthithucung.controller;

import com.sieuthithucung.dto.ShippingAddressDto;
import com.sieuthithucung.service.ShippingAddressService;
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
@RequestMapping("/api/v1/shipping-addresses")
public class ShippingAddressController {

    private final ShippingAddressService service;

    public ShippingAddressController(ShippingAddressService service) {
        this.service = service;
    }

    @PostMapping
    public ResponseEntity<ShippingAddressDto> create(@RequestBody ShippingAddressDto dto) {
        return ResponseEntity.status(HttpStatus.CREATED).body(service.create(dto));
    }

    @PutMapping("/{id}")
    public ResponseEntity<ShippingAddressDto> update(@PathVariable Long id, @RequestBody ShippingAddressDto dto) {
        return ResponseEntity.ok(service.update(id, dto));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> delete(@PathVariable Long id) {
        service.delete(id);
        return ResponseEntity.noContent().build();
    }

    @GetMapping("/{id}")
    public ResponseEntity<ShippingAddressDto> findById(@PathVariable Long id) {
        return ResponseEntity.ok(service.findById(id));
    }

    @GetMapping
    public ResponseEntity<List<ShippingAddressDto>> getAll() {
        return ResponseEntity.ok(service.getAll());
    }
}